<div>
    <x-breadcrumbs :items="[
        'Dashboard' => route('dashboard'),
        'Students' => route('students.index'),
        ($student ? 'Edit' : 'Add') => '',
    ]" />

    <div class="card" style="max-width: 600px;">
        <div class="card-header">{{ $student ? 'Edit Student' : 'Add Student' }}</div>
        <div class="card-body">
            <form wire:submit="save" novalidate>

                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model.blur="name" class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" wire:model.blur="email" class="form-control @error('email') is-invalid @enderror">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Course <span class="text-danger">*</span></label>
                    <select wire:model.blur="course_id" class="form-select @error('course_id') is-invalid @enderror">
                        <option value="">-- Select a course --</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">
                                {{ $course->name }} ({{ $course->seatsTaken() }}/{{ $course->capacity }} seats)
                            </option>
                        @endforeach
                    </select>
                    @error('course_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @if (!$student)
                    <div class="mb-3">
                        <label class="form-label">Enrollment Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model.blur="enrollment_date" class="form-control @error('enrollment_date') is-invalid @enderror">
                        @error('enrollment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select wire:model="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Save</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                    <a href="{{ route('students.index') }}" wire:navigate class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
