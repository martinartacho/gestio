<?php

namespace App\Filament\Resources\TeacherPaymentResource\Pages;

use App\Filament\Resources\TeacherPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTeacherPayments extends ListRecords
{
    protected static string $resource = TeacherPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('site.create'))];
    }
}
