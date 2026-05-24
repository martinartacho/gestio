<?php

namespace App\Filament\Resources\CampusStudents\Pages;

use App\Filament\Resources\CampusStudents\CampusStudentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCampusStudents extends ListRecords
{
    protected static string $resource = CampusStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
