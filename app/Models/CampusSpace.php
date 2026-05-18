<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampusSpace extends Model
{
    use HasFactory;
    protected $table = 'campus_spaces';

    protected $fillable = [
        'name',
        'code',
        'capacity',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'capacity'  => 'integer',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'sala_actes' => 'Sala d\'actes',
        'mitjana'    => 'Aula mitjana',
        'petita'     => 'Aula petita',
        'polivalent' => 'Polivalent',
        'extern'     => 'Extern',
        'virtual'    => 'Virtual (online)',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(CampusCourse::class, 'space_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $cap = $this->capacity > 0 ? " ({$this->capacity} places)" : '';
        return "{$this->name}{$cap}";
    }
}
