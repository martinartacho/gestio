<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Idioma per defecte triat a Ajustes → Avançat (site_settings.locale); si no n'hi ha, APP_LOCALE. */
    public function getDefaultLocaleAttribute(): string
    {
        return SiteSetting::where('tenant_id', $this->id)->where('key', 'locale')->first()?->value
            ?? config('app.locale');
    }
}
