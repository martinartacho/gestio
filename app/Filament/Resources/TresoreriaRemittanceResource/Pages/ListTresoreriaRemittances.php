<?php

namespace App\Filament\Resources\TresoreriaRemittanceResource\Pages;

use App\Filament\Resources\TresoreriaRemittanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTresoreriaRemittances extends ListRecords
{
    protected static string $resource = TresoreriaRemittanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
