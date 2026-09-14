<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Student;
use App\Services\EnrollmentService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * One component handles both "create" and "edit":
 *   Route::get('/students/create', StudentForm::class)
 *   Route::get('/students/{student}/edit', StudentForm::class)
 * When the route has a {student} segment, Livewire route-model-binds it into
 * mount() the same way a controller method would.
 */
#[Layout('layouts.app')]
class StudentForm extends Component
{
    public ?Student $student = null;

    public string $name = '';
    public string $email = '';
    public ?int $course_id = null;
    public string $status = 'active';
    public string $enrollment_date = '';

    public function mount(?Student $student = null): void
    {
        abort_unless(auth()->user()->can('edit-students'), 403);

        if ($student && $student->exists) {
            $this->student = $student;
            $this->name = $student->name;
            $this->email = $student->email;
            $this->status = $student->status;
            $this->course_id = $student->course_id;
        } else {
            $this->enrollment_date = now()->toDateString();
        }
    }

    protected function rules(): array
    {
        $studentId = $this->student?->id;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email' . ($studentId ? ",{$studentId}" : ''),
            'course_id' => 'required|exists:courses,id',
            'status' => 'required|in:active,inactive',
            // Only required when first enrolling — editing an existing student
            // doesn't need a new enrollment date unless they're changing courses.
            'enrollment_date' => $studentId ? 'nullable|date' : 'required|date',
        ];
    }

    protected function messages(): array
    {
        return [
            'course_id.required' => 'Please choose a course.',
        ];
    }

    // Real-time, per-field feedback as the user leaves each input
    // (wire:model.blur in the view triggers this via Livewire's `updated` hook).
    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function save(EnrollmentService $enrollmentService)
    {
        // The one line that actually enforces "can't submit if something is
        // missing": validate() throws and halts here — save() returns without
        // touching the database — whenever a required field is empty.
        $validated = $this->validate();

        try {
            if ($this->student) {
                $enrollmentService->changeCourse(
                    $this->student,
                    $validated['course_id'],
                    $validated['enrollment_date'] ?: now()->toDateString(),
                    [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'status' => $validated['status'],
                        'updated_by' => auth()->id(),
                    ]
                );
            } else {
                $enrollmentService->enrollNewStudent(
                    [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'status' => $validated['status'],
                        'updated_by' => auth()->id(),
                    ],
                    $validated['course_id'],
                    $validated['enrollment_date']
                );
            }
        } catch (\RuntimeException $e) {
            // e.g. "Course X is full" — a business-rule failure, not a bug.
            // Surface it next to the field it's about instead of a 500 page.
            $this->addError('course_id', $e->getMessage());
            return;
        }

        session()->flash('success', 'Student saved.');
        $this->redirect(route('students.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.student-form', [
            'courses' => Course::orderBy('name')->get(),
        ]);
    }
}
