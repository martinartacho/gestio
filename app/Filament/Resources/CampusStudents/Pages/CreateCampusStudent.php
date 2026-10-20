<?php

namespace App\Filament\Resources\CampusStudents\Pages;

use App\Filament\Resources\CampusStudents\CampusStudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCampusStudent extends CreateRecord
{
    protected static string $resource = CampusStudentResource::class;

    /**
     * Un alumne creat des del panel no passa pel registre: no té codi de verificació pendent, així que es dona el correu
     * per verificat. El consentiment de dades queda sense marcar (només el pot donar l'alumne).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['email_verified_at'] = now();
        $data['data_consent'] = false;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Filament no ho pot fer sol (relació N:M, no BelongsTo).
        if ($tenant = current_tenant()) {
            $this->record->tenants()->syncWithoutDetaching([$tenant->id]);
        }
    }
}
