<?php

namespace App\Filament\Resources\AssociatQuoteResource\Pages;

use App\Filament\Resources\AssociatQuoteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssociatQuote extends EditRecord
{
    protected static string $resource = AssociatQuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
