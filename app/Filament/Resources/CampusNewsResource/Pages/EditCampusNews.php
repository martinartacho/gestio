<?php

namespace App\Filament\Resources\CampusNewsResource\Pages;

use App\Filament\Resources\CampusNewsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCampusNews extends EditRecord
{
    protected static string $resource = CampusNewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
