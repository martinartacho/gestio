<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampusNews extends Model
{
    const LABELS = [
        'campus'     => 'Campus',
        'associats'  => 'Associats',
        'tresoreria' => 'Tresoreria',
        'secretaria' => 'Secretaria',
        'admin'      => 'Administració',
        'sistema'    => 'Sistema',
    ];

    protected $fillable = [
        'title',
        'summary',
        'body',
        'labels',
        'version',
        'published_at',
    ];

    protected $casts = [
        'labels'       => 'array',
        'published_at' => 'datetime',
    ];

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeByLabel($query, string $label)
    {
        return $query->whereJsonContains('labels', $label);
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }
}
