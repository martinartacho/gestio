<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // getRedirectUrl() no sobreescrit → Filament retorna null → queda a l'edit

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Guardar'),

            Actions\Action::make('saveAndReturn')
                ->label('Guardar i tornar')
                ->action(function (): void {
                    $this->save(shouldRedirect: false);
                    $this->redirect($this->previousUrl ?? $this->getResource()::getUrl('index'));
                })
                ->color('gray'),

            $this->getCancelFormAction(),
        ];
    }
}
