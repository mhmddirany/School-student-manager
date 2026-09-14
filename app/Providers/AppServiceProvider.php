<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Privilege definitions (roles: admin | staff | viewer). Checked both in
     * Blade (@can) and inside Livewire components (auth()->user()->can(...))
     * before any state-changing action — never rely on hiding a button alone.
     */
    public function boot(): void
    {
        Gate::define('manage-users', fn (User $user) => $user->role === 'admin');
        Gate::define('view-logs', fn (User $user) => $user->role === 'admin');
        Gate::define('delete-students', fn (User $user) => $user->role === 'admin');
        Gate::define('edit-students', fn (User $user) => in_array($user->role, ['admin', 'staff'], true));
    }
}
