<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

/**
 * Application-layer equivalent of the MySQL `enroll_student` stored procedure
 * documented in database/mysql_examples.sql (see the study guide, §6, for why
 * SQLite — this project's default DB — can't run that procedure directly).
 *
 * Same guarantee either way: check capacity and create the enrollment
 * atomically inside one transaction with a row lock on the course, so two
 * people enrolling into the last seat at the same instant can't both succeed.
 *
 * Each Student row also has its own AFTER INSERT / AFTER UPDATE trigger (see
 * the 000006 migration) that writes to activity_logs automatically — which is
 * why the two methods below each perform exactly one INSERT or one UPDATE on
 * `students`, not two. Firing either one twice would just double the log entries.
 */
class EnrollmentService
{
    /**
     * Create a brand-new student already enrolled in a course.
     *
     * @throws \RuntimeException if the course has no free seats
     */
    public function enrollNewStudent(array $studentData, int $courseId, string $enrolledAt): Student
    {
        return DB::transaction(function () use ($studentData, $courseId, $enrolledAt) {
            $course = Course::where('id', $courseId)->lockForUpdate()->firstOrFail();
            $this->assertHasSeat($course);

            // One INSERT on `students` → fires trg_students_after_insert once.
            $student = Student::create($studentData + ['course_id' => $course->id]);

            Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'enrolled_at' => $enrolledAt,
            ]);

            return $student;
        });
    }

    /**
     * Update an existing student, moving them to a (possibly different) course.
     * Only checks/consumes a seat if the course actually changed.
     *
     * @throws \RuntimeException if switching to a course with no free seats
     */
    public function changeCourse(Student $student, int $courseId, string $enrolledAt, array $otherAttributes = []): void
    {
        DB::transaction(function () use ($student, $courseId, $enrolledAt, $otherAttributes) {
            $course = Course::where('id', $courseId)->lockForUpdate()->firstOrFail();

            if ($student->course_id !== $course->id) {
                $this->assertHasSeat($course);
                Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'enrolled_at' => $enrolledAt,
                ]);
            }

            // One UPDATE on `students` → fires trg_students_after_update once.
            $student->update($otherAttributes + ['course_id' => $course->id]);
        });
    }

    private function assertHasSeat(Course $course): void
    {
        $seatsTaken = Enrollment::where('course_id', $course->id)->count();
        if ($seatsTaken >= $course->capacity) {
            throw new \RuntimeException("\"{$course->name}\" is full ({$course->capacity} seats taken).");
        }
    }
}
