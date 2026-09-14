<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Demonstrates a real VIEW and real TRIGGERs, written for SQLite (this project's
 * default driver, chosen so it runs with zero setup). The MySQL-flavoured
 * versions of the same three concepts — including a STORED PROCEDURE, which
 * SQLite does not support at all — live in database/mysql_examples.sql.
 *
 * Why triggers only handle INSERT/UPDATE, not DELETE: a delete trigger has no
 * reliable way to know *who* performed the delete (there's no NEW row, and
 * SQLite has no session variables to stash the current user in). That's why
 * student deletion is logged explicitly in app code instead —
 * see App\Livewire\StudentsTable::delete(). This trade-off is worth saying
 * out loud in an interview; see the study guide, §6.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            // On MySQL/Postgres, use database/mysql_examples.sql instead —
            // the syntax below is SQLite-specific.
            return;
        }

        DB::unprepared('
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
            LEFT JOIN courses c ON c.id = s.course_id
        ');

        DB::unprepared('
            CREATE TRIGGER trg_students_after_insert
            AFTER INSERT ON students
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, user_name, action, subject_type, subject_id, description, created_at)
                VALUES (
                    NEW.updated_by,
                    (SELECT name FROM users WHERE id = NEW.updated_by),
                    \'create\',
                    \'Student\',
                    NEW.id,
                    \'Created student \' || NEW.name,
                    datetime(\'now\')
                );
            END
        ');

        DB::unprepared('
            CREATE TRIGGER trg_students_after_update
            AFTER UPDATE ON students
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, user_name, action, subject_type, subject_id, description, created_at)
                VALUES (
                    NEW.updated_by,
                    (SELECT name FROM users WHERE id = NEW.updated_by),
                    \'update\',
                    \'Student\',
                    NEW.id,
                    \'Updated student \' || NEW.name,
                    datetime(\'now\')
                );
            END
        ');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_students_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_students_after_update');
        DB::unprepared('DROP VIEW IF EXISTS student_enrollment_summary');
    }
};
