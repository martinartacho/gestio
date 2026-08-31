<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\CampusTeacher;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTeachers extends ListRecords
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('attachExistingTeacher')
                ->label('Afegir professor existent')
                ->icon('heroicon-o-user-plus')
                ->color('gray')
                // Només super-admin: la cerca és global (nom/email de totes
                // les institucions) — un admin d'un sol tenant no hauria de
                // poder veure ni buscar professorat d'altres entitats.
                ->visible(fn () => auth()->user()?->hasRole('super-admin') ?? false)
                ->modalHeading('Afegir un professor d\'una altra institució')
                ->modalDescription('Cerca per nom o email un professor que ja té compte (a qualsevol institució) i afegeix-lo a aquesta.')
                ->form([
                    Select::make('teacher_id')
                        ->label('Professor')
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            abort_unless(auth()->user()?->hasRole('super-admin'), 403);

                            return CampusTeacher::query()
                                ->where(fn ($q) => $q
                                    ->where('email', 'like', "%{$search}%")
                                    ->orWhere('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%"))
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn (CampusTeacher $t) => [$t->id => "{$t->full_name} ({$t->email})"]);
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $t = CampusTeacher::find($value);
                            return $t ? "{$t->full_name} ({$t->email})" : null;
                        })
                        ->required(),
                ])
                ->action(function (array $data): void {
                    abort_unless(auth()->user()?->hasRole('super-admin'), 403);

                    $teacher = CampusTeacher::findOrFail($data['teacher_id']);
                    $tenant  = current_tenant();

                    if ($teacher->belongsToTenant($tenant?->id)) {
                        Notification::make()
                            ->title('Aquest professor ja pertany a aquesta institució')
                            ->warning()
                            ->send();
                        return;
                    }

                    $teacher->tenants()->attach($tenant->id);

                    Notification::make()
                        ->title("{$teacher->full_name} afegit/da a {$tenant->name}")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
