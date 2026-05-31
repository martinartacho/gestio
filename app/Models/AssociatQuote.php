<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociatQuote extends Model
{
    protected $table = 'associat_quotes';

    protected $fillable = [
        'member_id', 'remittance_id', 'year', 'period', 'period_number',
        'amount', 'status', 'due_date', 'paid_at', 'failure_reason',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(AssociatMember::class, 'member_id');
    }

    public function remittance(): BelongsTo
    {
        return $this->belongsTo(AssociatSepaRemittance::class, 'remittance_id');
    }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isPaid(): bool    { return $this->status === 'paid'; }
}
