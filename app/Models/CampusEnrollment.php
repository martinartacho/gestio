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
        'payment_reference', 'payment_expires_at', 'reminder_sent_at',
        'stripe_session_id', 'stripe_payment_intent', 'stripe_refund_id',
        'paid_at', 'refunded_amount', 'refunded_at', 'refund_notes', 'notes',
        'first_name', 'last_name', 'email', 'phone', 'dni',
        'enrollment_date', 'bank_iban', 'bank_holder',
        'rgpd_accepted', 'rgpd_accepted_at',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'paid_at'             => 'datetime',
        'refunded_amount'     => 'decimal:2',
        'refunded_at'         => 'datetime',
        'payment_expires_at'  => 'datetime',
        'reminder_sent_at'    => 'datetime',
        'enrollment_date'     => 'date',
        'rgpd_accepted'       => 'boolean',
        'rgpd_accepted_at'    => 'datetime',
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

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Genera una referència alfanumèrica única (8 caràcters majúscules).
     * Reintenta fins a trobar una de lliure.
     */
    public static function generateReference(): string
    {
        do {
            $ref = strtoupper(\Illuminate\Support\Str::random(8));
        } while (static::where('payment_reference', $ref)->exists());

        return $ref;
    }

    /** Retorna true si el temps per pagar ha expirat i l'estat continua pendent. */
    public function isExpired(): bool
    {
        return $this->status === 'pending'
            && $this->payment_expires_at !== null
            && now()->gt($this->payment_expires_at);
    }

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
