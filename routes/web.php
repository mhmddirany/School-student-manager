<?php

use App\Livewire\ActivityLogs;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\StudentForm;
use App\Livewire\StudentsTable;
use App\Livewire\UsersManager;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(auth()->check() ? '/dashboard' : '/login'));

// 'guest' / 'auth' / 'can:...' are default Laravel middleware aliases —
// registered by the framework itself, nothing extra to configure.
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    ActivityLog::record('logout', null, 'User logged out');
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/students', StudentsTable::class)->name('students.index');
    Route::get('/students/create', StudentForm::class)->name('students.create');
    Route::get('/students/{student}/edit', StudentForm::class)->name('students.edit');

    Route::get('/users', UsersManager::class)->middleware('can:manage-users')->name('users.index');
    Route::get('/logs', ActivityLogs::class)->middleware('can:view-logs')->name('logs.index');
});
