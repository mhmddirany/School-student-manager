<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin-only screen: create users and assign their privilege level (role),
 * and delete users. Route is protected with ->middleware('can:manage-users')
 * in routes/web.php; mount() re-checks it so the component is never reachable
 * from anywhere else without the same gate passing.
 */
#[Layout('layouts.app')]
class UsersManager extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'viewer';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage-users'), 403);
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,staff,viewer',
        ];
    }

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function createUser(): void
    {
        $validated = $this->validate(); // blocks creation until every required field is present & valid

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        ActivityLog::record('create', $user, "Created user {$user->name} ({$user->role})");

        $this->reset(['name', 'email', 'password', 'role']);
        session()->flash('success', "User \"{$user->name}\" created.");
    }

    public function deleteUser(int $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', "You can't delete your own account.");
            return;
        }

        $user = User::findOrFail($userId);
        $name = $user->name;
        $user->delete();

        ActivityLog::record('delete', $user, "Deleted user {$name}");
        session()->flash('success', "User \"{$name}\" deleted.");
    }

    public function render()
    {
        return view('livewire.users-manager', [
            'users' => User::orderBy('id')->get(),
        ]);
    }
}
