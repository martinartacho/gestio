<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampusEnrollment extends Model
{
    use HasFactory;

    protected $table = 'campus_enrollments';

    protected $fillable = [
        'student_id', 'course_id', 'status', 'amount',
        'stripe_session_id', 'stripe_payment_intent', 'paid_at', 'notes',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending'   => 'Pendent',
        'paid'      => 'Pagat',
        'cancelled' => 'Cancel·lat',
        'refunded'  => 'Retornat',
    ];

    public const STATUS_COLORS = [
        'pending'   => 'warning',
        'paid'      => 'success',
        'cancelled' => 'danger',
        'refunded'  => 'gray',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(CampusStudent::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(CampusCourse::class, 'course_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
