<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsLessonResponse extends Model
{
    protected $fillable = [
        'lesson_id',
        'student_id',
        'question_index',
        'question_type',
        'response_text',
        'response_choices',
        'response_bool',
        'score',
        'auto_graded',
        'submitted_at',
    ];

    protected $casts = [
        'question_index'   => 'integer',
        'response_choices' => 'array',
        'response_bool'    => 'boolean',
        'score'            => 'decimal:2',
        'auto_graded'      => 'boolean',
        'submitted_at'     => 'datetime',
    ];

    // ─── Relacions ────────────────────────────────────────────────────────────

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(LmsLesson::class, 'lesson_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(CampusStudent::class, 'student_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Retorna true/false si la resposta ha estat auto-avaluada, null si no és avaluable.
     */
    public function isCorrect(): ?bool
    {
        if (! $this->auto_graded || $this->score === null) {
            return null;
        }

        return $this->score > 0;
    }

    /**
     * Retorna true si l'alumne ha enviat alguna resposta (de qualsevol tipus).
     */
    public function hasResponse(): bool
    {
        return $this->response_text !== null
            || $this->response_choices !== null
            || $this->response_bool !== null;
    }

    /**
     * Retorna la resposta en format llegible per mostrar a la vista.
     */
    public function getDisplayValue(): string
    {
        return match ($this->question_type) {
            'yes_no'  => $this->response_bool ? 'Sí' : 'No',
            'choice_one' => is_array($this->response_choices)
                ? implode(', ', $this->response_choices)
                : ($this->response_text ?? '—'),
            'choice_many' => is_array($this->response_choices)
                ? implode(', ', $this->response_choices)
                : '—',
            default => $this->response_text ?? '—',
        };
    }
}
