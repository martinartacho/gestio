<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CampusDocument extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'campus_documents';

    protected $fillable = [
        'tenant_id',
        'title', 'description', 'type',
        'file_path', 'file_name', 'file_size', 'mime_type',
        'url',
        'course_id', 'teacher_id', 'created_by',
        'visibility', 'inherit_to_editions',
        'available_from', 'session_number',
        'sort_order', 'status',
    ];

    protected $casts = [
        'inherit_to_editions' => 'boolean',
        'available_from'      => 'datetime',
        'file_size'           => 'integer',
        'session_number'      => 'integer',
        'sort_order'          => 'integer',
    ];

    // ── Constants ──────────────────────────────────────────────────────────

    public const TYPES = [
        'file' => 'Fitxer',
        'url'  => 'Enllaç extern',
    ];

    public const VISIBILITIES = [
        'public'   => 'Públic',
        'enrolled' => 'Alumnes matriculats',
        'private'  => 'Privat (professor/admin)',
    ];

    public const VISIBILITY_COLORS = [
        'public'   => 'success',
        'enrolled' => 'info',
        'private'  => 'gray',
    ];

    public const STATUSES = [
        'active' => 'Actiu',
        'draft'  => 'Esborrany',
    ];

    /** Formats acceptats i mida màxima */
    public const ACCEPTED_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
    ];

    public const MAX_FILE_SIZE_KB = 10240; // 10 MB

    // ── Relacions ──────────────────────────────────────────────────────────

    public function course(): BelongsTo
    {
        return $this->belongsTo(CampusCourse::class, 'course_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(CampusTeacher::class, 'teacher_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVisible($query)
    {
        return $query->where('status', 'active')
                     ->where(fn($q) => $q->whereNull('available_from')
                                         ->orWhere('available_from', '<=', now()));
    }

    public function scopePublic($query)
    {
        return $query->visible()->where('visibility', 'public');
    }

    public function scopeForEnrolled($query)
    {
        return $query->visible()->whereIn('visibility', ['public', 'enrolled']);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function isUrl(): bool
    {
        return $this->type === 'url';
    }

    /**
     * El document és accessible ara per a alumnes?
     * Lògica OR: cap condició → sí; si n'hi ha → n'hi ha prou amb que una es compleixi.
     * Requereix que 'course' estigui carregat si hi ha session_number.
     */
    public function isAvailableForStudents(): bool
    {
        $conditions = [];

        if ($this->available_from) {
            $conditions[] = now()->gte($this->available_from);
        }

        if ($this->session_number) {
            $course = $this->relationLoaded('course') ? $this->course : null;
            $done   = $course?->sessionsPast() ?? 0;
            $conditions[] = $done >= $this->session_number;
        }

        return empty($conditions) || in_array(true, $conditions, true);
    }

    /**
     * Text descriptiu de PER QUÈ el document encara no és accessible.
     * Retorna null si el document ja és accessible.
     * Requereix que 'course' estigui carregat si hi ha session_number.
     */
    public function getNotAvailableReasonAttribute(): ?string
    {
        if ($this->isAvailableForStudents()) return null;

        $parts = [];

        if ($this->available_from && now()->lt($this->available_from)) {
            $parts[] = 'des del ' . $this->available_from->format('d/m/Y H:i');
        }

        if ($this->session_number) {
            $course = $this->relationLoaded('course') ? $this->course : null;
            $done   = $course?->sessionsPast() ?? 0;
            if ($done < $this->session_number) {
                $parts[] = 'sessió ' . $this->session_number . ' (fetes: ' . $done . ')';
            }
        }

        if (empty($parts)) return null;

        return '⏰ ' . implode(' o ', $parts);
    }

    /** @deprecated Usar isAvailableForStudents() */
    public function isAvailable(): bool
    {
        return $this->status === 'active'
            && ($this->available_from === null || $this->available_from->lte(now()));
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (! $this->file_size) return '—';
        $kb = $this->file_size / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';
        return round($kb / 1024, 2) . ' MB';
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->type === 'url') return $this->url;
        if ($this->file_path && Storage::disk('local')->exists($this->file_path)) {
            // El tenant del document primer: un alumne pot veure a la mateixa
            // pàgina (Els meus cursos) documents de més d'una institució —
            // current_tenant() només serveix de reserva (p. ex. des de
            // l'admin, on URL::defaults(tenant) no s'ha establert).
            return route('campus.documents.download', [
                'tenant'   => $this->tenant?->slug ?? current_tenant()?->slug,
                'document' => $this->id,
            ]);
        }
        return null;
    }

    public function getIconAttribute(): string
    {
        if ($this->type === 'url') return 'heroicon-o-link';

        return match (true) {
            str_contains($this->mime_type ?? '', 'pdf')         => 'heroicon-o-document-text',
            str_contains($this->mime_type ?? '', 'word')        => 'heroicon-o-document',
            str_contains($this->mime_type ?? '', 'excel')
            || str_contains($this->mime_type ?? '', 'sheet')    => 'heroicon-o-table-cells',
            str_contains($this->mime_type ?? '', 'powerpoint')
            || str_contains($this->mime_type ?? '', 'presentation') => 'heroicon-o-presentation-chart-bar',
            str_contains($this->mime_type ?? '', 'image')       => 'heroicon-o-photo',
            default                                              => 'heroicon-o-paper-clip',
        };
    }
}
