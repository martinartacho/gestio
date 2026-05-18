<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CampusCategory extends Model
{
    use HasFactory;
    protected $table = 'campus_categories';

    protected $fillable = [
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

    public function courses(): HasMany
    {
        return $this->hasMany(CampusCourse::class, 'category_id');
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
}
