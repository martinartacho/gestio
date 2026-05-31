<?php

namespace App\Filament\Resources\AssociatQuoteResource\Pages;

use App\Filament\Resources\AssociatQuoteResource;
use App\Models\AssociatMember;
use App\Models\AssociatQuote;
use App\Settings\SettingStore;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAssociatQuotes extends ListRecords
{
    protected static string $resource = AssociatQuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_quotes')
                ->label('Generar quotes')
                ->icon('heroicon-o-bolt')
                ->color('primary')
                ->modalHeading('Generar quotes per als socis actius')
                ->modalDescription('Es crearan quotes per a tots els socis actius que no en tinguin per al període seleccionat.')
                ->form([
                    Select::make('period')
                        ->label('Periodicitat')
                        ->options([
                            'annual'      => 'Anual (1 quota / any)',
                            'semi-annual' => 'Semestral (2 quotes / any)',
                            'quarterly'   => 'Trimestral (4 quotes / any)',
                            'monthly'     => 'Mensual (12 quotes / any)',
                        ])
                        ->default('annual')
                        ->live()
                        ->afterStateUpdated(function (string $state, callable $set): void {
                            $base = (float) app(SettingStore::class)->get('associat_quota_amount', 0);
                            $set('amount', match ($state) {
                                'semi-annual' => round($base / 2, 2),
                                'quarterly'   => round($base / 4, 2),
                                'monthly'     => round($base / 12, 2),
                                default       => $base,
                            });
                        })
                        ->required(),

                    TextInput::make('year')
                        ->label('Any')
                        ->numeric()
                        ->minValue(2020)
                        ->maxValue(2099)
                        ->default(now()->year)
                        ->required(),

                    TextInput::make('amount')
                        ->label('Import per quota (€)')
                        ->numeric()
                        ->default(fn () => (float) app(SettingStore::class)->get('associat_quota_amount', 0))
                        ->required(),

                    DatePicker::make('first_due_date')
                        ->label('Data de venciment (primera quota)')
                        ->helperText('Les quotes següents es calculen automàticament a partir d\'aquesta data.')
                        ->displayFormat('d/m/Y'),
                ])
                ->action(function (array $data): void {
                    $year         = (int) $data['year'];
                    $amount       = (float) $data['amount'];
                    $period       = $data['period'];
                    $firstDueDate = isset($data['first_due_date']) ? Carbon::parse($data['first_due_date']) : null;

                    $numPeriods = match ($period) {
                        'semi-annual' => 2,
                        'quarterly'   => 4,
                        'monthly'     => 12,
                        default       => 1,
                    };

                    $monthsInterval = match ($period) {
                        'semi-annual' => 6,
                        'quarterly'   => 3,
                        'monthly'     => 1,
                        default       => 0,
                    };

                    $existingMemberIds = AssociatQuote::where('year', $year)
                        ->where('period', $period)
                        ->pluck('member_id')
                        ->all();

                    $members = AssociatMember::where('status', 'active')
                        ->whereNotIn('id', $existingMemberIds)
                        ->get();

                    if ($members->isEmpty()) {
                        Notification::make()
                            ->title('Sense socis pendents')
                            ->body("Tots els socis actius ja tenen quotes {$period} per a l'any {$year}.")
                            ->warning()
                            ->send();
                        return;
                    }

                    $created = 0;
                    foreach ($members as $member) {
                        for ($i = 1; $i <= $numPeriods; $i++) {
                            $dueDate = $firstDueDate
                                ? $firstDueDate->copy()->addMonths(($i - 1) * $monthsInterval)->toDateString()
                                : null;

                            AssociatQuote::create([
                                'member_id'     => $member->id,
                                'year'          => $year,
                                'period'        => $period,
                                'period_number' => $i,
                                'amount'        => $amount,
                                'status'        => 'pending',
                                'due_date'      => $dueDate,
                            ]);
                            $created++;
                        }
                    }

                    Notification::make()
                        ->title('Quotes generades')
                        ->body("{$created} quotes creades per a {$members->count()} socis (any {$year}).")
                        ->success()
                        ->send();
                }),

            CreateAction::make(),
        ];
    }
}
