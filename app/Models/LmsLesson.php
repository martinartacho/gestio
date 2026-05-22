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
        // Blocs JSON (visualització)
        'concepts',
        'text_cards',
        'comparison',
        'reflection_questions',
        'exercise',
        // Preguntes interactives (avaluables o obertes)
        'questions',
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
        'questions'            => 'array',
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

    public function responses(): HasMany
    {
        return $this->hasMany(LmsLessonResponse::class, 'lesson_id');
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

    /**
     * Retorna les preguntes d'un bloc concret (reflection, exercise, quiz).
     */
    public function getQuestionsOfBlock(string $block): array
    {
        if (empty($this->questions)) {
            return [];
        }

        return array_values(
            array_filter($this->questions, fn ($q) => ($q['block'] ?? '') === $block)
        );
    }

    /**
     * Retorna la resposta d'un alumne per a una pregunta concreta (per index).
     */
    public function getResponseFor(CampusStudent $student, int $questionIndex): ?LmsLessonResponse
    {
        return $this->responses()
            ->where('student_id', $student->id)
            ->where('question_index', $questionIndex)
            ->first();
    }

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
