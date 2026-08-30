<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'tenant_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'active'            => 'boolean',
        ];
    }

    /**
     * Controla quién puede acceder al panel de Filament.
     * En el MVP solo los admin; amplía con más roles si necesitas.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active && $this->hasAnyRole(['super-admin', 'admin', 'manager', 'tresoreria', 'secretaria', 'editor']);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** Tenants que aquest usuari pot triar en fer login. Super-admin els veu tots. */
    public function getTenants(Panel $panel): Collection
    {
        if ($this->hasRole('super-admin')) {
            return Tenant::all();
        }

        return $this->tenant ? collect([$this->tenant]) : collect();
    }

    public function getDefaultTenant(Panel $panel): ?EloquentModel
    {
        return $this->tenant ?? ($this->hasRole('super-admin') ? Tenant::first() : null);
    }

    public function canAccessTenant(EloquentModel $tenant): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->tenant_id === $tenant->id;
    }
}