<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampusPayment extends Model
{
    use HasFactory;
    protected $table = 'campus_payments';

    protected $fillable = [
        'enrollment_id',
        'amount', 'method', 'payment_date', 'status', 'reference',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    public const METHODS = [
        'transfer'     => 'Transferència',
        'cash'         => 'Efectiu',
        'card'         => 'Targeta',
        'domiciliacio' => 'Domiciliació bancària',
    ];

    public const STATUSES = [
        'pending'   => 'Pendent',
        'completed' => 'Completat',
        'refunded'  => 'Retornat',
    ];

    public const STATUS_COLORS = [
        'pending'   => 'warning',
        'completed' => 'success',
        'refunded'  => 'danger',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CampusEnrollment::class, 'enrollment_id');
    }
}
