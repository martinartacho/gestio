<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssociatSepaRemittance extends Model
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
        return $this->hasMany(AssociatQuote::class, 'remittance_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isGenerated(): bool
    {
        return in_array($this->status, ['generated', 'submitted', 'processed']);
    }

    public static function nextReference(int $year): string
    {
        $count = static::where('year', $year)->count() + 1;
        return sprintf('REMESA-%d-%03d', $year, $count);
    }
}
