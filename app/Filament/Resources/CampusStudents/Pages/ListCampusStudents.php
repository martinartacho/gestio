<?php

namespace App\Filament\Resources\CampusStudents\Pages;

use App\Filament\Resources\CampusStudents\CampusStudentResource;
use App\Models\CampusStudent;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCampusStudents extends ListRecords
{
    protected static string $resource = CampusStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('attachExistingStudent')
                ->label('Afegir alumne existent')
                ->icon('heroicon-o-user-plus')
                ->color('gray')
                // Només super-admin: la cerca és global (nom/email de totes
                // les institucions) — un admin d'un sol tenant no hauria de
                // poder veure ni buscar alumnes d'altres entitats.
                ->visible(fn () => auth()->user()?->hasRole('super-admin') ?? false)
                ->modalHeading('Afegir un alumne d\'una altra institució')
                ->modalDescription('Cerca per nom o email un alumne que ja té compte (a qualsevol institució) i afegeix-lo a aquesta.')
                ->form([
                    Select::make('student_id')
                        ->label('Alumne')
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            // El desplegable de cerca és un endpoint Livewire a
                            // part — cal el mateix guard, no només ->visible().
                            abort_unless(auth()->user()?->hasRole('super-admin'), 403);

                            return CampusStudent::query()
                                ->where(fn ($q) => $q
                                    ->where('email', 'like', "%{$search}%")
                                    ->orWhere('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%"))
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn (CampusStudent $s) => [$s->id => "{$s->full_name} ({$s->email})"]);
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $s = CampusStudent::find($value);
                            return $s ? "{$s->full_name} ({$s->email})" : null;
                        })
                        ->required(),
                ])
                ->action(function (array $data): void {
                    // ->visible() només amaga el botó — cal tornar a comprovar
                    // el rol aquí per si l'acció s'invoca directament.
                    abort_unless(auth()->user()?->hasRole('super-admin'), 403);

                    $student = CampusStudent::findOrFail($data['student_id']);
                    $tenant  = current_tenant();

                    if ($student->belongsToTenant($tenant?->id)) {
                        Notification::make()
                            ->title('Aquest alumne ja pertany a aquesta institució')
                            ->warning()
                            ->send();
                        return;
                    }

                    $student->tenants()->attach($tenant->id);

                    Notification::make()
                        ->title("{$student->full_name} afegit/da a {$tenant->name}")
                        ->success()
                        ->send();
                }),

            CreateAction::make(),
        ];
    }
}
