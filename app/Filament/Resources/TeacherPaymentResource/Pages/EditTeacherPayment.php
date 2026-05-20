<?php

namespace App\Filament\Resources\TeacherPaymentResource\Pages;

use App\Filament\Resources\TeacherPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTeacherPayment extends EditRecord
{
    protected static string $resource = TeacherPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label(__('site.delete'))];
    }
}
