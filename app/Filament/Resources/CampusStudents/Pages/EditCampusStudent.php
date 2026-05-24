<?php

namespace App\Filament\Resources\CampusStudents\Pages;

use App\Filament\Resources\CampusStudents\CampusStudentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCampusStudent extends EditRecord
{
    protected static string $resource = CampusStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
