<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampusTeacher extends Model
{
    use HasFactory;
    protected $table = 'campus_teachers';

    protected $fillable = [
        'user_id', 'code', 'first_name', 'last_name', 'email', 'phone', 'specialization', 'bio', 'status',
        'dni', 'address', 'postal_code', 'city', 'observacions',
        'degree', 'title', 'areas', 'hiring_date',
        'fiscal_situation', 'needs_payment', 'invoice', 'payment_type',
        'bank_iban', 'bank_holder', 'fiscal_id',
        'beneficiary_dni', 'beneficiary_iban', 'beneficiary_holder',
        'beneficiary_fiscal_situation', 'beneficiary_invoice', 'beneficiary_city', 'beneficiary_postal_code',
        'data_consent', 'fiscal_responsibility', 'ceded_confirmation',
        'payment_status', 'payment_confirmed_at', 'payment_pdf_path',
        'metadata',
    ];

    protected $casts = [
        'areas'                  => 'array',
        'metadata'               => 'array',
        'bank_iban'              => 'encrypted',
        'bank_holder'            => 'encrypted',
        'fiscal_id'              => 'encrypted',
        'beneficiary_iban'       => 'encrypted',
        'beneficiary_holder'     => 'encrypted',
        'needs_payment'          => 'boolean',
        'invoice'                => 'boolean',
        'beneficiary_invoice'    => 'boolean',
        'data_consent'           => 'boolean',
        'fiscal_responsibility'  => 'boolean',
        'ceded_confirmation'     => 'boolean',
        'hiring_date'            => 'date',
        'payment_confirmed_at'   => 'datetime',
    ];

    public const STATUSES = [
        'active'   => 'Actiu',
        'inactive' => 'Inactiu',
    ];

    public const PAYMENT_STATUSES = [
        'pending'   => 'Pendent',
        'confirmed' => 'Confirmat',
        'paid'      => 'Pagat',
        'cancelled' => 'Cancel·lat',
    ];

    public const PAYMENT_STATUS_COLORS = [
        'pending'   => 'warning',
        'confirmed' => 'info',
        'paid'      => 'success',
        'cancelled' => 'danger',
    ];

    public const FISCAL_SITUATIONS = [
        'autonom'    => 'Autònom/a',
        'empresa'    => 'Empresa',
        'entitat'    => 'Entitat',
        'particular' => 'Particular',
    ];

    public const PAYMENT_TYPES = [
        'own'         => 'Compte propi',
        'beneficiary' => 'Compte beneficiari',
        'waived'      => 'Renúncia al pagament',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(
            CampusCourse::class,
            'campus_course_teacher',
            'teacher_id',
            'course_id'
        )->withPivot('role', 'sessions_assigned')
         ->withTimestamps();
    }

    public function activeCourses(): BelongsToMany
    {
        return $this->courses()->wherePivot('role', 'main');
    }

    public function teacherPayments(): HasMany
    {
        return $this->hasMany(CampusTeacherPayment::class, 'teacher_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
