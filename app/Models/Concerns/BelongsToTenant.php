<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Relació comuna per a qualsevol model amb columna tenant_id. Filament la fa
 * servir (via el nom per defecte "tenant") per filtrar automàticament els
 * registres de cada Resource segons el tenant actiu al panell.
 */
trait BelongsToTenant
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
