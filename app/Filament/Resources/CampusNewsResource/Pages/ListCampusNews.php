<?php

namespace App\Filament\Resources\CampusNewsResource\Pages;

use App\Filament\Resources\CampusNewsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCampusNews extends ListRecords
{
    protected static string $resource = CampusNewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nova notícia'),
        ];
    }
}
