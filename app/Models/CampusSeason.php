<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampusSeason extends Model
{
    use HasFactory;
    protected $table = 'campus_seasons';

    protected $fillable = [
        'name',
        'year',
        'quadrimester',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'year'         => 'integer',
        'quadrimester' => 'integer',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'is_active'    => 'boolean',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(CampusCourse::class, 'season_id');
    }

    public function getQuadrimesterLabelAttribute(): string
    {
        return match($this->quadrimester) {
            1 => 'Tardor (set–gen)',
            2 => 'Primavera (feb–jun)',
            default => "Q{$this->quadrimester}",
        };
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name} · {$this->quadrimester_label}";
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
