<?php

namespace App\Filament\Resources\SeasonResource\Pages;

use App\Filament\Resources\SeasonResource;
use App\Models\CampusSeason;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSeasons extends ListRecords
{
    protected static string $resource = SeasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_academic_year')
                ->label(__('site.create_academic_year'))
                ->icon('heroicon-o-academic-cap')
                ->color('success')
                ->modalHeading(__('site.create_academic_year'))
                ->modalWidth('md')
                ->form([
                    TextInput::make('year')
                        ->label(__('site.season_year'))
                        ->numeric()
                        ->required()
                        ->minValue(2020)
                        ->maxValue(2050)
                        ->default(now()->year),

                    Select::make('type')
                        ->label(__('site.season_period_type'))
                        ->options([
                            '2q' => __('site.season_type_2q'),
                            '3t' => __('site.season_type_3t'),
                            '4t' => __('site.season_type_4t'),
                            '9m' => __('site.season_type_9m'),
                        ])
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    $year = (int) $data['year'];
                    $y1   = $year + 1;

                    $periods = match ($data['type']) {
                        '2q' => [
                            [1, "Q1 Tardor {$year}",    "{$year}-09-01", "{$y1}-01-31"],
                            [2, "Q2 Primavera {$year}", "{$y1}-02-01",   "{$y1}-06-30"],
                        ],
                        '3t' => [
                            [1, "T1 – {$year}", "{$year}-09-01", "{$year}-11-30"],
                            [2, "T2 – {$year}", "{$year}-12-01", "{$y1}-02-28"],
                            [3, "T3 – {$year}", "{$y1}-03-01",   "{$y1}-05-31"],
                        ],
                        '4t' => [
                            [1, "T1 – {$year}", "{$year}-09-01", "{$year}-10-31"],
                            [2, "T2 – {$year}", "{$year}-11-01", "{$y1}-01-31"],
                            [3, "T3 – {$year}", "{$y1}-02-01",   "{$y1}-03-31"],
                            [4, "T4 – {$year}", "{$y1}-04-01",   "{$y1}-05-31"],
                        ],
                        '9m' => [
                            [1, "Set {$year}", "{$year}-09-01", "{$year}-09-30"],
                            [2, "Oct {$year}", "{$year}-10-01", "{$year}-10-31"],
                            [3, "Nov {$year}", "{$year}-11-01", "{$year}-11-30"],
                            [4, "Des {$year}", "{$year}-12-01", "{$year}-12-31"],
                            [5, "Gen {$y1}",   "{$y1}-01-01",   "{$y1}-01-31"],
                            [6, "Feb {$y1}",   "{$y1}-02-01",   "{$y1}-02-28"],
                            [7, "Mar {$y1}",   "{$y1}-03-01",   "{$y1}-03-31"],
                            [8, "Abr {$y1}",   "{$y1}-04-01",   "{$y1}-04-30"],
                            [9, "Mai {$y1}",   "{$y1}-05-01",   "{$y1}-05-31"],
                        ],
                    };

                    $created = 0;
                    $skipped = 0;

                    foreach ($periods as [$q, $name, $start, $end]) {
                        // Sense tenant_id a la cerca, trobava (o topava amb)
                        // la temporada d'un altre tenant amb el mateix
                        // any+quadrimestre en lloc de crear la pròpia.
                        $season = CampusSeason::firstOrCreate(
                            ['tenant_id' => current_tenant()?->id, 'year' => $year, 'quadrimester' => $q],
                            ['name' => $name, 'start_date' => $start, 'end_date' => $end, 'status' => 'draft']
                        );
                        $season->wasRecentlyCreated ? $created++ : $skipped++;
                    }

                    $body = __('site.academic_year_created', ['year' => $year, 'count' => $created]);
                    if ($skipped > 0) {
                        $body .= ' ' . __('site.academic_year_skipped', ['count' => $skipped]);
                    }

                    Notification::make()
                        ->title($body)
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
