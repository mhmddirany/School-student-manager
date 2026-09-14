<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The project's main "Livewire data table" showcase: live search, a course
 * filter, a status filter, sortable columns, and pagination — see the study
 * guide, §5, for the line-by-line explanation this mirrors.
 */
#[Layout('layouts.app')]
class StudentsTable extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public ?int $courseFilter = null;

    public string $statusFilter = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCourseFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'courseFilter', 'statusFilter']);
        $this->resetPage();
    }

    /**
     * Deletes are logged explicitly here rather than by a DB trigger — see the
     * comment in the 000006 migration for why deletes are the one case a
     * trigger can't cleanly attribute to a user.
     */
    public function delete(int $studentId): void
    {
        abort_unless(auth()->user()->can('delete-students'), 403);

        $student = Student::findOrFail($studentId);
        $name = $student->name;
        $student->delete();

        ActivityLog::record('delete', $student, "Deleted student {$name}");
        session()->flash('success', "Deleted {$name}.");
    }

    public function render()
    {
        $students = Student::query()
            ->with('course')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->courseFilter, fn ($query) => $query->where('course_id', $this->courseFilter))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(8);

        return view('livewire.students-table', [
            'students' => $students,
            'courses' => Course::orderBy('name')->get(),
        ]);
    }
}
