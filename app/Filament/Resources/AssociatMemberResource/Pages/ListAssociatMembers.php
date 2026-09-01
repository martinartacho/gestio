<?php

namespace App\Filament\Resources\AssociatMemberResource\Pages;

use App\Filament\Resources\AssociatMemberResource;
use App\Models\AssociatMember;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAssociatMembers extends ListRecords
{
    protected static string $resource = AssociatMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('attachExistingMember')
                ->label('Afegir soci existent')
                ->icon('heroicon-o-user-plus')
                ->color('gray')
                // Només super-admin: la cerca és global (nom/email de totes
                // les institucions) — un admin d'un sol tenant no hauria de
                // poder veure ni buscar socis d'altres entitats.
                ->visible(fn () => auth()->user()?->hasRole('super-admin') ?? false)
                ->modalHeading('Afegir un soci d\'una altra institució')
                ->modalDescription('Cerca per nom o email un soci que ja té compte (a qualsevol institució) i afegeix-lo a aquesta.')
                ->form([
                    Select::make('member_id')
                        ->label('Soci')
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            abort_unless(auth()->user()?->hasRole('super-admin'), 403);

                            return AssociatMember::query()
                                ->where(fn ($q) => $q
                                    ->where('email', 'like', "%{$search}%")
                                    ->orWhere('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%"))
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn (AssociatMember $m) => [$m->id => "{$m->full_name} ({$m->email})"]);
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $m = AssociatMember::find($value);
                            return $m ? "{$m->full_name} ({$m->email})" : null;
                        })
                        ->required(),
                ])
                ->action(function (array $data): void {
                    abort_unless(auth()->user()?->hasRole('super-admin'), 403);

                    $member = AssociatMember::findOrFail($data['member_id']);
                    $tenant = current_tenant();

                    if ($member->belongsToTenant($tenant?->id)) {
                        Notification::make()
                            ->title('Aquest soci ja pertany a aquesta institució')
                            ->warning()
                            ->send();
                        return;
                    }

                    $member->tenants()->attach($tenant->id);

                    Notification::make()
                        ->title("{$member->full_name} afegit/da a {$tenant->name}")
                        ->success()
                        ->send();
                }),

            CreateAction::make(),
        ];
    }
}
