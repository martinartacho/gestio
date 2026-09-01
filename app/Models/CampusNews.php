<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CampusNews extends Model
{
    use BelongsToTenant;

    const LABELS = [
        'campus'     => 'Campus',
        'associats'  => 'Associats',
        'tresoreria' => 'Tresoreria',
        'secretaria' => 'Secretaria',
        'admin'      => 'Administració',
        'sistema'    => 'Sistema',
    ];

    const RECIPIENTS = [
        'all'      => 'Tots (públic)',
        'private'  => 'Privat (panel)',
        'teachers' => 'Professorat',
        'students' => 'Alumnat',
        'members'  => 'Associats',
    ];

    protected $fillable = [
        'tenant_id',
        'title',
        'summary',
        'body',
        'labels',
        'version',
        'recipients',
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

    /**
     * Filtra les notícies visibles per a l'usuari actual segons el seu context d'autenticació.
     * 'all' és sempre visible. Els altres valors requereixen autenticació al guarda corresponent.
     */
    public function scopeVisibleForCurrentUser($query)
    {
        $allowed = ['all'];

        if (auth('student')->check()) {
            $allowed[] = 'students';
        }
        if (auth('teacher')->check()) {
            $allowed[] = 'teachers';
        }
        if (auth('member')->check()) {
            $allowed[] = 'members';
        }
        if (auth('web')->check() && ! auth('web')->user()?->hasRole('viewer')) {
            $allowed[] = 'private';
        }

        return $query->whereIn('recipients', $allowed);
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
