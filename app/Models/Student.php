<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    // Deliberately NOT including `updated_by` here as always-mass-assignable —
    // it's set explicitly by the caller (see StudentForm::save()) so it can't be
    // spoofed by unexpected form input. This is the kind of guard an AI-assisted
    // suggestion tends to skip; see the study guide, §7.
    protected $fillable = ['name', 'email', 'course_id', 'status', 'updated_by'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
