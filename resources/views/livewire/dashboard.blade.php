<div>
    <x-breadcrumbs :items="['Dashboard' => '']" />

    <h3 class="mb-4">Welcome, {{ auth()->user()->name }}</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-value">{{ $studentCount }}</div>
                <div class="stat-label">Total Students</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-value">{{ $activeStudentCount }}</div>
                <div class="stat-label">Active</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-value">{{ $courseCount }}</div>
                <div class="stat-label">Courses</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="stat-value">{{ $enrollmentCount }}</div>
                <div class="stat-label">Enrollments</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Quick Links</div>
                <div class="card-body">
                    <a href="{{ route('students.index') }}" wire:navigate class="btn btn-outline-primary btn-sm me-2 mb-2">View Students</a>
                    @can('edit-students')
                        <a href="{{ route('students.create') }}" wire:navigate class="btn btn-outline-success btn-sm me-2 mb-2">Add Student</a>
                    @endcan
                    @can('manage-users')
                        <a href="{{ route('users.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm me-2 mb-2">Manage Users</a>
                    @endcan
                    @can('view-logs')
                        <a href="{{ route('logs.index') }}" wire:navigate class="btn btn-outline-dark btn-sm mb-2">View Activity Logs</a>
                    @endcan
                </div>
            </div>
        </div>

        @can('view-logs')
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Recent Activity</div>
                <ul class="list-group list-group-flush">
                    @forelse ($recentLogs as $log)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>
                                <strong>{{ $log->user_name }}</strong>
                                {{ $log->action }} {{ $log->subject_type }}
                                {{ $log->subject_id ? '#' . $log->subject_id : '' }}
                            </span>
                            <small class="text-muted">{{ $log->created_at }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No activity yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endcan
    </div>
</div>
