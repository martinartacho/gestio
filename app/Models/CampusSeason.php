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
        'start_date_enrollment',
        'end_date_enrollment',
        'status',
    ];

    protected $casts = [
        'year'                  => 'integer',
        'quadrimester'          => 'integer',
        'start_date'            => 'date',
        'end_date'              => 'date',
        'start_date_enrollment' => 'date',
        'end_date_enrollment'   => 'date',
    ];

    public const STATUSES = [
        'draft'  => 'En preparació',
        'active' => 'Activa',
        'closed' => 'Tancada',
    ];

    public const STATUS_COLORS = [
        'draft'  => 'gray',
        'active' => 'success',
        'closed' => 'danger',
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
        return static::where('status', 'active')->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isPast(): bool
    {
        return now()->startOfDay()->gt($this->end_date);
    }

    public function isFuture(): bool
    {
        return now()->startOfDay()->lt($this->start_date);
    }

    public function enrollmentIsOpen(): bool
    {
        $today = now()->startOfDay();
        if ($this->start_date_enrollment && $today->lt($this->start_date_enrollment)) {
            return false;
        }
        if ($this->end_date_enrollment && $today->gt($this->end_date_enrollment)) {
            return false;
        }
        return true;
    }
}
