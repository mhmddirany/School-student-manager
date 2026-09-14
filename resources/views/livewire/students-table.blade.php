<div>
    <x-breadcrumbs :items="['Dashboard' => route('dashboard'), 'Students' => '']" />

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Students</h3>
        @can('edit-students')
            <a href="{{ route('students.create') }}" wire:navigate class="btn btn-success">+ Add Student</a>
        @endcan
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name or email...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="courseFilter" class="form-select">
                        <option value="">All courses</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">Clear</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach (['name' => 'Name', 'email' => 'Email', 'status' => 'Status'] as $field => $label)
                            <th wire:click="sortBy('{{ $field }}')" class="sortable {{ $sortField === $field ? 'active' : '' }}">
                                {{ $label }}
                                <span class="sort-arrow">{{ $sortField === $field ? ($sortDirection === 'asc' ? '▲' : '▼') : '⇅' }}</span>
                            </th>
                        @endforeach
                        <th>Course</th>
                        @canany(['edit-students', 'delete-students'])
                            <th style="width:160px;">Actions</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr wire:key="student-{{ $student->id }}">
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>
                                <span class="badge {{ $student->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $student->status }}
                                </span>
                            </td>
                            <td>{{ $student->course->name ?? '—' }}</td>
                            @canany(['edit-students', 'delete-students'])
                                <td>
                                    @can('edit-students')
                                        <a href="{{ route('students.edit', $student) }}" wire:navigate class="btn btn-sm btn-outline-primary">Edit</a>
                                    @endcan
                                    @can('delete-students')
                                        <button
                                            wire:click="delete({{ $student->id }})"
                                            wire:confirm="Delete {{ $student->name }}? This cannot be undone."
                                            class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    @endcan
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No students match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">{{ $students->total() }} student(s) found</small>
            {{ $students->links() }}
        </div>
    </div>
</div>
