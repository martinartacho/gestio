<?php

namespace App\Filament\Resources\TresoreriaQuoteResource\Pages;

use App\Filament\Resources\TresoreriaQuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTresoreriaQuotes extends ListRecords
{
    protected static string $resource = TresoreriaQuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
