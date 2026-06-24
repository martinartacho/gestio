<?php

namespace App\Filament\Resources\CampusNewsResource\Pages;

use App\Filament\Resources\CampusNewsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCampusNews extends CreateRecord
{
    protected static string $resource = CampusNewsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
