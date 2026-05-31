<?php

namespace App\Filament\Resources\AssociatSepaRemittanceResource\Pages;

use App\Filament\Resources\AssociatSepaRemittanceResource;
use App\Models\AssociatSepaRemittance;
use Filament\Resources\Pages\CreateRecord;

class CreateAssociatSepaRemittance extends CreateRecord
{
    protected static string $resource = AssociatSepaRemittanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['reference'])) {
            $data['reference'] = AssociatSepaRemittance::nextReference((int) ($data['year'] ?? now()->year));
        }
        return $data;
    }
}
