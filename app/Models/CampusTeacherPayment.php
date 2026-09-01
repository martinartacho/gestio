<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampusTeacherPayment extends Model
{
    use BelongsToTenant;

    protected $table = 'campus_teacher_payments';

    protected $fillable = [
        'tenant_id',
        'season_id', 'teacher_id', 'course_id',
        'sessions_description', 'sessions_count', 'price_per_session',
        'gross_amount', 'retention_pct', 'retention_amount', 'net_amount',
        'issues_invoice', 'status', 'sent_at', 'paid_at', 'notes',
    ];

    protected $casts = [
        'issues_invoice'   => 'boolean',
        'sent_at'          => 'datetime',
        'paid_at'          => 'datetime',
        'price_per_session' => 'decimal:2',
        'gross_amount'     => 'decimal:2',
        'retention_pct'    => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'net_amount'       => 'decimal:2',
    ];

    public const STATUSES = [
        'draft'     => 'Esborrany',
        'sent'      => 'Enviat',
        'paid'      => 'Pagat',
        'cancelled' => 'Cancel·lat',
    ];

    public const STATUS_COLORS = [
        'draft'     => 'gray',
        'sent'      => 'info',
        'paid'      => 'success',
        'cancelled' => 'danger',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(CampusSeason::class, 'season_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(CampusTeacher::class, 'teacher_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(CampusCourse::class, 'course_id');
    }
}
