<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ActivityLogs extends Component
{
    use WithPagination;

    public string $actionFilter = '';
    public string $subjectFilter = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('view-logs'), 403);
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSubjectFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::query()
            ->when($this->actionFilter, fn ($q) => $q->where('action', $this->actionFilter))
            ->when($this->subjectFilter, fn ($q) => $q->where('subject_type', $this->subjectFilter))
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.activity-logs', ['logs' => $logs]);
    }
}
