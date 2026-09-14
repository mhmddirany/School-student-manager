<?php

namespace App\Models;

// Illuminate\Foundation\Auth\User is the base class Laravel's own fresh-install
// User model already extends — keep that line as-is from the skeleton and just
// add what's below (role, casts, helpers). See README "Layering" step 3.

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // admin | staff | viewer
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
