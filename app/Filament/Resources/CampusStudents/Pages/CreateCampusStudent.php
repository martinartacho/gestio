<?php

namespace App\Filament\Resources\CampusStudents\Pages;

use App\Filament\Resources\CampusStudents\CampusStudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCampusStudent extends CreateRecord
{
    protected static string $resource = CampusStudentResource::class;

    protected function afterCreate(): void
    {
        // Filament no ho pot fer sol (relació N:M, no BelongsTo).
        if ($tenant = current_tenant()) {
            $this->record->tenants()->syncWithoutDetaching([$tenant->id]);
        }
    }
}
