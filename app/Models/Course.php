<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'capacity'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function seatsTaken(): int
    {
        return $this->enrollments()->count();
    }

    public function hasAvailableSeats(): bool
    {
        return $this->seatsTaken() < $this->capacity;
    }
}
