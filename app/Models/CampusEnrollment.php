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
        'course_id',
        'first_name', 'last_name', 'email', 'phone', 'dni',
        'status', 'enrollment_date',
        'bank_iban', 'bank_holder',
        'rgpd_accepted', 'rgpd_accepted_at',
        'notes',
    ];

    protected $casts = [
        'enrollment_date'  => 'date',
        'rgpd_accepted'    => 'boolean',
        'rgpd_accepted_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending'   => 'Pendent',
        'confirmed' => 'Confirmada',
        'cancelled' => 'Cancel·lada',
        'completed' => 'Completada',
    ];

    public const STATUS_COLORS = [
        'pending'   => 'warning',
        'confirmed' => 'success',
        'cancelled' => 'danger',
        'completed' => 'gray',
    ];

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
        return "{$this->first_name} {$this->last_name}";
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }
}
