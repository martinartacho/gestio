<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TresoreriaRemittance extends Model
{
    protected $table = 'associat_sepa_remittances';

    protected $fillable = [
        'reference', 'year', 'execution_date',
        'total_amount', 'total_transactions',
        'status', 'xml_path', 'generated_at', 'notes',
    ];

    protected $casts = [
        'execution_date' => 'date',
        'generated_at'   => 'datetime',
        'total_amount'   => 'decimal:2',
    ];

    public function quotes(): HasMany
    {
        return $this->hasMany(TresoreriaQuote::class, 'remittance_id');
    }
}
