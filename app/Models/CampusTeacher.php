<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CampusTeacher extends Model
{
    use HasFactory;
    protected $table = 'campus_teachers';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'specialization',
        'bio',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public const STATUSES = [
        'active'   => 'Actiu',
        'inactive' => 'Inactiu',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(
            CampusCourse::class,
            'campus_course_teacher',
            'teacher_id',
            'course_id'
        )->withPivot('role', 'sessions_assigned')
         ->withTimestamps();
    }

    public function activeCourses(): BelongsToMany
    {
        return $this->courses()->wherePivot('role', 'main');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
