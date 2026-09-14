<div>
    <x-breadcrumbs :items="['Dashboard' => route('dashboard'), 'Activity Logs' => '']" />

    <h3 class="mb-3">Activity Logs</h3>
    <p class="text-muted">
        Student create/update rows come from the <code>AFTER INSERT</code> / <code>AFTER UPDATE</code>
        database triggers; logins, user management, and student deletes are logged from application
        code — see the study guide, §6, for why.
    </p>

    <div class="row g-2 mb-3">
        <div class="col-auto">
            <select wire:model.live="actionFilter" class="form-select form-select-sm">
                <option value="">All actions</option>
                @foreach (['login', 'logout', 'create', 'update', 'delete'] as $a)
                    <option value="{{ $a }}">{{ ucfirst($a) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select wire:model.live="subjectFilter" class="form-select form-select-sm">
                <option value="">All types</option>
                <option value="Student">Student</option>
                <option value="User">User</option>
            </select>
        </div>
    </div>

    <div class="card">
        <table class="table table-sm table-striped mb-0">
            <thead class="table-light">
                <tr><th>When</th><th>User</th><th>Action</th><th>Subject</th><th>Details</th></tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr wire:key="log-{{ $log->id }}">
                        <td class="text-nowrap">{{ $log->created_at }}</td>
                        <td>{{ $log->user_name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $log->action }}</span></td>
                        <td>{{ $log->subject_type }}{{ $log->subject_id ? ' #' . $log->subject_id : '' }}</td>
                        <td class="text-muted">{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No matching activity.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="card-footer">{{ $logs->links() }}</div>
    </div>
</div>
