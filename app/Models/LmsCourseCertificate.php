<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsCourseCertificate extends Model
{
    protected $fillable = [
        'course_id',
        'student_id',
        'issued_at',
        'certificate_number',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    // ─── Boot: genera certificate_number automàticament ───────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $cert) {
            if (empty($cert->issued_at)) {
                $cert->issued_at = now();
            }

            if (empty($cert->certificate_number)) {
                $cert->certificate_number = static::generateCertNumber(
                    $cert->course_id,
                    $cert->student_id
                );
            }
        });
    }

    protected static function generateCertNumber(int $courseId, int $studentId): string
    {
        $year      = now()->format('Y');
        $courseRef = str_pad($courseId, 6, '0', STR_PAD_LEFT);
        $stuRef    = str_pad($studentId, 6, '0', STR_PAD_LEFT);

        return "CERT-{$year}-{$courseRef}-{$stuRef}";
    }

    // ─── Relacions ────────────────────────────────────────────────────────────

    public function course(): BelongsTo
    {
        return $this->belongsTo(CampusCourse::class, 'course_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(CampusStudent::class, 'student_id');
    }
}
