<?php

namespace App\Filament\Resources\TresoreriaRemittanceResource\Pages;

use App\Filament\Resources\TresoreriaRemittanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTresoreriaRemittance extends EditRecord
{
    protected static string $resource = TresoreriaRemittanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
