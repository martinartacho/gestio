<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampusEnrollment extends Model
{
    use HasFactory;
    protected $table = 'campus_enrollments';

    protected $fillable = [
        'student_id', 'course_id', 'status', 'payment_method', 'amount',
        'stripe_session_id', 'stripe_payment_intent', 'paid_at', 'notes',
        'first_name', 'last_name', 'email', 'phone', 'dni',
        'enrollment_date', 'bank_iban', 'bank_holder',
        'rgpd_accepted', 'rgpd_accepted_at',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'paid_at'          => 'datetime',
        'enrollment_date'  => 'date',
        'rgpd_accepted'    => 'boolean',
        'rgpd_accepted_at' => 'datetime',
    ];

    public const PAYMENT_METHODS = [
        'stripe'   => 'Targeta (Stripe)',
        'transfer' => 'Transferència bancària',
        'bizum'    => 'Bizum',
        'cash'     => 'Efectiu',
        'paypal'   => 'PayPal',
        'free'     => 'Gratuït',
    ];

    public const STATUSES = [
        'pending'   => 'Pendent',
        'paid'      => 'Pagat',
        'confirmed' => 'Confirmada',
        'cancelled' => 'Cancel·lada',
        'completed' => 'Completada',
        'refunded'  => 'Retornat',
    ];

    public const STATUS_COLORS = [
        'pending'   => 'warning',
        'paid'      => 'success',
        'confirmed' => 'success',
        'cancelled' => 'danger',
        'completed' => 'gray',
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

    public function payments(): HasMany
    {
        return $this->hasMany(CampusPayment::class, 'enrollment_id');
    }

    public function getFullNameAttribute(): string
    {
        if ($this->student) {
            return $this->student->full_name;
        }
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'confirmed']);
    }

    /** Retorna true si el mètode de pagament requereix confirmació manual. */
    public function isManualPayment(): bool
    {
        return ! in_array($this->payment_method, ['stripe', 'free', null]);
    }
}
