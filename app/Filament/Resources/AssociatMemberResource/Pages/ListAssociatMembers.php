<?php

namespace App\Filament\Resources\AssociatMemberResource\Pages;

use App\Filament\Resources\AssociatMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssociatMembers extends ListRecords
{
    protected static string $resource = AssociatMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
