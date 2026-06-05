<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsLessonProgress extends Model
{
    protected $fillable = [
        'lesson_id',
        'student_id',
        'verb',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(LmsLesson::class, 'lesson_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(CampusStudent::class, 'student_id');
    }
}
