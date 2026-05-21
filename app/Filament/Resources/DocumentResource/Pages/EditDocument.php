<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\CampusDocument;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function (CampusDocument $record) {
                    if ($record->file_path) {
                        Storage::disk('local')->delete($record->file_path);
                    }
                }),
        ];
    }
}
