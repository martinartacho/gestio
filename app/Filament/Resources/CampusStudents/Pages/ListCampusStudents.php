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
                ->modalHeading('Afegir un alumne d\'una altra institució')
                ->modalDescription('Cerca per nom o email un alumne que ja té compte (a qualsevol institució) i afegeix-lo a aquesta.')
                ->form([
                    Select::make('student_id')
                        ->label('Alumne')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => CampusStudent::query()
                            ->where(fn ($q) => $q
                                ->where('email', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%"))
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn (CampusStudent $s) => [$s->id => "{$s->full_name} ({$s->email})"]))
                        ->getOptionLabelUsing(function ($value) {
                            $s = CampusStudent::find($value);
                            return $s ? "{$s->full_name} ({$s->email})" : null;
                        })
                        ->required(),
                ])
                ->action(function (array $data): void {
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
