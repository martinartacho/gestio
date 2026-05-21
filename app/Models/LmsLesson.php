<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class LmsLesson extends Model
{
    use HasFactory;
    protected $fillable = [
        'course_id',
        'session_number',
        'title',
        'subtitle',
        'duration',
        // Introducció
        'quote_text',
        'quote_author',
        'quote_work',
        'intro_text',
        // Tema
        'topic_text',
        // Blocs JSON
        'concepts',
        'text_cards',
        'comparison',
        'reflection_questions',
        'exercise',
        // Control
        'status',
        'sort_order',
    ];

    protected $casts = [
        'session_number'       => 'integer',
        'sort_order'           => 'integer',
        'concepts'             => 'array',
        'text_cards'           => 'array',
        'comparison'           => 'array',
        'reflection_questions' => 'array',
        'exercise'             => 'array',
    ];

    // ─── Relacions ────────────────────────────────────────────────────────────

    public function course(): BelongsTo
    {
        return $this->belongsTo(CampusCourse::class, 'course_id');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(LmsLessonProgress::class, 'lesson_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('session_number');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isCompletedBy(CampusStudent $student): bool
    {
        return $this->progresses()
            ->where('student_id', $student->id)
            ->exists();
    }

    public function getSessionLabelAttribute(): string
    {
        return 'Sessió ' . $this->session_number;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
