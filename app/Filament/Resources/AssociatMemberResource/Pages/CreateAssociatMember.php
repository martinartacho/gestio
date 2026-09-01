<?php

namespace App\Filament\Resources\AssociatMemberResource\Pages;

use App\Filament\Resources\AssociatMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssociatMember extends CreateRecord
{
    protected static string $resource = AssociatMemberResource::class;

    protected function afterCreate(): void
    {
        // Filament no ho pot fer sol (relació N:M, no BelongsTo).
        if ($tenant = current_tenant()) {
            $this->record->tenants()->syncWithoutDetaching([$tenant->id]);
        }
    }
}
