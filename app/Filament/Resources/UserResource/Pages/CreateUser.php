<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        // Filament associa sol el tenant actual del panell en crear (via
        // l'esdeveniment 'creating' del model), sobreescrivint qualsevol
        // tenant_id que el formulari ja hagués enviat — cal reafirmar la
        // tria explícita del super-admin després que Filament hagi acabat.
        if (array_key_exists('tenant_id', $this->data)) {
            $this->record->update(['tenant_id' => $this->data['tenant_id']]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Usuario creado correctamente';
    }
}