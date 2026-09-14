-- ============================================================================
-- Views, Stored Procedures & Triggers — worked MySQL examples
-- ============================================================================
-- Same domain as this project (students / courses / enrollments /
-- activity_logs), written in real MySQL syntax so you have something to run
-- on an actual MySQL server. The Laravel app itself runs on SQLite by
-- default (zero setup) and only implements the VIEW and the TRIGGERs for
-- real — SQLite has no stored procedures at all, which is exactly why this
-- file exists. See the study guide, §6, for the full explanation.
--
-- To try this for real: `mysql -u root -p < mysql_examples.sql` against an
-- empty database, or paste sections into a MySQL client one at a time.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS student_manager_demo CHARACTER SET utf8mb4;
USE student_manager_demo;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'viewer') NOT NULL DEFAULT 'viewer'
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    capacity INT NOT NULL DEFAULT 20
);

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    course_id INT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    updated_by INT NULL,               -- the app sets this before every save
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at DATE NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    user_name VARCHAR(255),
    action VARCHAR(50) NOT NULL,
    subject_type VARCHAR(50),
    subject_id INT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (name, role) VALUES ('Admin User', 'admin');
INSERT INTO courses (name, capacity) VALUES ('Mathematics 101', 2); -- tiny capacity, to demo the "full" error easily


-- ============================================================================
-- 1) VIEW — a saved SELECT you can query like a table.
--    Use it to hide a multi-join report behind one simple name.
-- ============================================================================

CREATE VIEW student_enrollment_summary AS
SELECT
    s.id            AS student_id,
    s.name          AS student_name,
    s.status        AS student_status,
    c.id            AS course_id,
    c.name          AS course_name,
    c.capacity      AS course_capacity,
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS seats_taken
FROM students s
LEFT JOIN courses c ON c.id = s.course_id;

-- SELECT * FROM student_enrollment_summary WHERE course_name = 'Mathematics 101';


-- ============================================================================
-- 2) STORED PROCEDURE — a named, precompiled block of SQL that runs inside
--    the database itself. Encapsulates "check capacity, then enroll" as one
--    atomic, reusable operation — exactly what
--    App\Services\EnrollmentService::enrollNewStudent() does in application
--    code instead, since SQLite can't run this.
-- ============================================================================

DELIMITER //

CREATE PROCEDURE enroll_student(
    IN p_student_id INT,
    IN p_course_id INT,
    IN p_enrolled_at DATE
)
BEGIN
    DECLARE current_count INT;
    DECLARE course_capacity INT;

    -- START TRANSACTION + an explicit row lock (FOR UPDATE) is the SQL-level
    -- equivalent of Eloquent's lockForUpdate() in EnrollmentService — it
    -- stops two simultaneous calls from both reading "1 seat left" and both
    -- succeeding.
    START TRANSACTION;

    SELECT capacity INTO course_capacity
    FROM courses
    WHERE id = p_course_id
    FOR UPDATE;

    SELECT COUNT(*) INTO current_count
    FROM enrollments
    WHERE course_id = p_course_id;

    IF current_count >= course_capacity THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Course is full';
    ELSE
        INSERT INTO enrollments (student_id, course_id, enrolled_at)
        VALUES (p_student_id, p_course_id, p_enrolled_at);

        UPDATE students SET course_id = p_course_id WHERE id = p_student_id;

        COMMIT;
    END IF;
END //

DELIMITER ;

-- Try it:
-- INSERT INTO students (name, email) VALUES ('Test Student', 'test@example.com');
-- CALL enroll_student(1, 1, CURDATE());  -- succeeds (capacity is 2, 0 taken)
-- CALL enroll_student(1, 1, CURDATE());  -- succeeds (1 taken, room for 1 more)
-- CALL enroll_student(1, 1, CURDATE());  -- fails: "Course is full"

-- From Laravel: DB::statement('CALL enroll_student(?, ?, ?)', [$studentId, $courseId, $date]);


-- ============================================================================
-- 3) TRIGGER — fires automatically on INSERT/UPDATE/DELETE. Used here for
--    audit logging, same idea as the SQLite triggers in this project's
--    2024_01_01_000006 migration, just in MySQL's syntax (DELIMITER, NEW.col
--    works the same way; MySQL also gives you OLD.col on UPDATE/DELETE,
--    which SQLite has too but this project doesn't need).
-- ============================================================================

DELIMITER //

CREATE TRIGGER trg_students_after_insert
AFTER INSERT ON students
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, user_name, action, subject_type, subject_id, description, created_at)
    SELECT NEW.updated_by, u.name, 'create', 'Student', NEW.id,
           CONCAT('Created student ', NEW.name), NOW()
    FROM users u WHERE u.id = NEW.updated_by;
END //

CREATE TRIGGER trg_students_after_update
AFTER UPDATE ON students
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, user_name, action, subject_type, subject_id, description, created_at)
    SELECT NEW.updated_by, u.name, 'update', 'Student', NEW.id,
           CONCAT('Updated student ', NEW.name), NOW()
    FROM users u WHERE u.id = NEW.updated_by;
END //

DELIMITER ;

-- Try it:
-- UPDATE students SET updated_by = 1, status = 'inactive' WHERE id = 1;
-- SELECT * FROM activity_logs ORDER BY id DESC;   -- an "update" row appears automatically,
--                                                   -- with no application code involved.

-- Note on DELETE: intentionally no delete trigger here — see the migration
-- comment in the Laravel project for why deletes are logged from app code
-- instead (there's no reliable "who" to attribute a delete trigger to).
