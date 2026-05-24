<?php

namespace App\Filament\Resources\CampusStudents\Pages;

use App\Filament\Resources\CampusStudents\CampusStudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCampusStudent extends CreateRecord
{
    protected static string $resource = CampusStudentResource::class;
}
