<div>
    <x-breadcrumbs :items="['Dashboard' => route('dashboard'), 'Users' => '']" />

    <h3 class="mb-3">Users &amp; Privileges</h3>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">Existing Users</div>
                <table class="table mb-0">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr wire:key="user-{{ $u->id }}">
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td><span class="badge bg-info text-dark text-uppercase">{{ $u->role }}</span></td>
                                <td>
                                    @if ($u->id !== auth()->id())
                                        <button
                                            wire:click="deleteUser({{ $u->id }})"
                                            wire:confirm="Delete {{ $u->name }}?"
                                            class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    @else
                                        <span class="text-muted small">(you)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Add User</div>
                <div class="card-body">
                    <form wire:submit="createUser" novalidate>
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
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" wire:model.blur="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role (privilege level)</label>
                            <select wire:model="role" class="form-select">
                                <option value="viewer">Viewer — read-only</option>
                                <option value="staff">Staff — manage students</option>
                                <option value="admin">Admin — full access</option>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Create User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
