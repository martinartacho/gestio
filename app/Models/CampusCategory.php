<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CampusCategory extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'campus_categories';

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'color',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    // ── Relacions ──────────────────────────────────────────────────────────
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CampusCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CampusCategory::class, 'parent_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(CampusCourse::class, 'category_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    /** Retorna el color hexadecimal per a ús en CSS inline. */
    public function getColorHexAttribute(): string
    {
        return self::COLOR_HEX[$this->color] ?? '#6b7280';
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public const COLORS = [
        'gray'   => 'Gris',
        'red'    => 'Vermell',
        'orange' => 'Taronja',
        'yellow' => 'Groc',
        'green'  => 'Verd',
        'blue'   => 'Blau',
        'indigo' => 'Índigo',
        'purple' => 'Violeta',
        'pink'   => 'Rosa',
    ];

    public const COLOR_HEX = [
        'gray'   => '#6b7280',
        'red'    => '#ef4444',
        'orange' => '#f97316',
        'yellow' => '#eab308',
        'green'  => '#22c55e',
        'blue'   => '#3b82f6',
        'indigo' => '#6366f1',
        'purple' => '#a855f7',
        'pink'   => '#ec4899',
    ];
}
