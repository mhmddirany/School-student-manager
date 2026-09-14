<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false; // `created_at` is set explicitly / by the DB trigger, no `updated_at` column exists

    protected $fillable = ['user_id', 'user_name', 'action', 'subject_type', 'subject_id', 'description', 'created_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an action from application code.
     *
     * Only used where a DB trigger can't do the job — user login/logout,
     * user management, and student *deletion* (see the migration comment in
     * 2024_01_01_000006_create_student_view_and_triggers.php for why deletes
     * are the one case triggers don't cover well here). Student create/update
     * are logged by the AFTER INSERT / AFTER UPDATE triggers instead — don't
     * call this for those or you'll get duplicate log rows.
     */
    public static function record(string $action, $subject = null, string $description = ''): void
    {
        static::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'guest',
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
