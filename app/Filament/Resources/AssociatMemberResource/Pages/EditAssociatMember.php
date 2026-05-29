<?php

namespace App\Filament\Resources\AssociatMemberResource\Pages;

use App\Filament\Resources\AssociatMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssociatMember extends EditRecord
{
    protected static string $resource = AssociatMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
