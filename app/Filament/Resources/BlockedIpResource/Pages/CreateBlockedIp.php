<?php

namespace App\Filament\Resources\BlockedIpResource\Pages;

use App\Filament\Resources\BlockedIpResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlockedIp extends CreateRecord
{
    protected static string $resource = BlockedIpResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['blocked_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
