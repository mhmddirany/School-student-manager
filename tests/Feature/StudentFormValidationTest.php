<?php

namespace Tests\Feature;

use App\Livewire\StudentForm;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Proves the "can't submit if something is missing" behaviour is real —
 * not just a client-side JS trick. Run with `php artisan test` once this
 * project is installed (see README — this can't run inside the sandbox that
 * produced it, since it needs the actual Laravel/Livewire vendor packages).
 */
class StudentFormValidationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_submitting_with_a_missing_required_field_does_not_create_a_student(): void
    {
        $this->actingAs($this->admin());
        $course = Course::create(['name' => 'Math', 'code' => 'M1', 'capacity' => 10]);

        Livewire::test(StudentForm::class)
            ->set('name', '') // required field left empty
            ->set('email', 'test@example.com')
            ->set('course_id', $course->id)
            ->set('enrollment_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['name' => 'required']);

        $this->assertDatabaseCount('students', 0);
    }

    public function test_a_fully_filled_form_creates_the_student_and_enrolls_them(): void
    {
        $this->actingAs($this->admin());
        $course = Course::create(['name' => 'Math', 'code' => 'M1', 'capacity' => 10]);

        Livewire::test(StudentForm::class)
            ->set('name', 'Ada Lovelace')
            ->set('email', 'ada@example.com')
            ->set('course_id', $course->id)
            ->set('enrollment_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('students', ['email' => 'ada@example.com', 'course_id' => $course->id]);
        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id]);
    }

    public function test_enrolling_past_capacity_is_rejected_without_a_second_seat_being_created(): void
    {
        $this->actingAs($this->admin());
        $course = Course::create(['name' => 'Tiny Class', 'code' => 'T1', 'capacity' => 1]);

        // Fill the only seat directly.
        $existing = Student::factory()->create(['course_id' => $course->id]);
        \App\Models\Enrollment::create([
            'student_id' => $existing->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        Livewire::test(StudentForm::class)
            ->set('name', 'Late Comer')
            ->set('email', 'late@example.com')
            ->set('course_id', $course->id)
            ->set('enrollment_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['course_id']);

        // The student row itself is never created when enrollment fails —
        // the whole thing is one DB transaction (see EnrollmentService).
        $this->assertDatabaseMissing('students', ['email' => 'late@example.com']);
        $this->assertEquals(1, \App\Models\Enrollment::where('course_id', $course->id)->count());
    }
}
