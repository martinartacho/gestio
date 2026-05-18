<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampusTimeSlot extends Model
{
    use HasFactory;
    protected $table = 'campus_time_slots';

    protected $fillable = [
        'day_of_week',
        'code',
        'start_time',
        'end_time',
        'description',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_active'   => 'boolean',
    ];

    public const DAYS = [
        1 => 'Dilluns',
        2 => 'Dimarts',
        3 => 'Dimecres',
        4 => 'Dijous',
        5 => 'Divendres',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(CampusCourse::class, 'time_slot_id');
    }

    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? "Dia {$this->day_of_week}";
    }

    public function getDisplayAttribute(): string
    {
        $start = substr($this->start_time, 0, 5);
        $end   = substr($this->end_time, 0, 5);
        return "{$this->day_name} {$start}–{$end}";
    }
}
