<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function afterCreate(): void
    {
        // Filament no ho pot fer sol (relació N:M, no BelongsTo).
        if ($tenant = current_tenant()) {
            $this->record->tenants()->syncWithoutDetaching([$tenant->id]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
