<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        return view('livewire.dashboard', [
            'studentCount' => Student::count(),
            'activeStudentCount' => Student::where('status', 'active')->count(),
            'courseCount' => Course::count(),
            'enrollmentCount' => Enrollment::count(),
            'recentLogs' => $user->can('view-logs') ? ActivityLog::latest('id')->limit(6)->get() : collect(),
        ]);
    }
}
