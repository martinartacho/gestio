<?php

namespace App\Filament\Resources\AssociatSepaRemittanceResource\Pages;

use App\Filament\Resources\AssociatSepaRemittanceResource;
use App\Models\AssociatQuote;
use App\Models\AssociatSepaRemittance;
use App\Services\SepaXmlGenerator;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListAssociatSepaRemittances extends ListRecords
{
    protected static string $resource = AssociatSepaRemittanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_remittance')
                ->label('Nova remesa SEPA')
                ->icon('heroicon-o-plus-circle')
                ->color('indigo')
                ->modalHeading('Generar nova remesa SEPA')
                ->modalDescription('Es crearà una remesa amb les quotes pendents del període seleccionat que tinguin mandat SEPA configurat.')
                ->form([
                    TextInput::make('year')
                        ->label('Any')
                        ->numeric()
                        ->default(now()->year)
                        ->required(),

                    Select::make('period')
                        ->label('Periodicitat')
                        ->options([
                            'annual'      => 'Anual',
                            'semi-annual' => 'Semestral',
                            'quarterly'   => 'Trimestral',
                            'monthly'     => 'Mensual',
                        ])
                        ->default('annual')
                        ->live()
                        ->afterStateUpdated(fn (callable $set) => $set('period_number', '1'))
                        ->required(),

                    Select::make('period_number')
                        ->label('Període específic')
                        ->options(fn (Get $get) => static::periodNumberOptions($get('period') ?? 'annual'))
                        ->default('1')
                        ->live()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $year         = (int) $data['year'];
                    $period       = $data['period'];
                    $periodNumber = (int) $data['period_number'];

                    $quotes = AssociatQuote::with('member')
                        ->where('year', $year)
                        ->where('period', $period)
                        ->where('period_number', $periodNumber)
                        ->where('status', 'pending')
                        ->whereNull('remittance_id')
                        ->whereHas('member', fn ($q) =>
                            $q->whereNotNull('bank_iban')
                              ->whereNotNull('mandate_reference')
                              ->where('status', 'active')
                        )
                        ->get()
                        ->filter(fn ($q) =>
                            !empty(preg_replace('/\s+/', '', $q->member->bank_iban ?? '')) &&
                            !empty($q->member->mandate_reference)
                        );

                    if ($quotes->isEmpty()) {
                        Notification::make()
                            ->title('Sense quotes pendents')
                            ->body('No hi ha quotes pendents amb mandat SEPA configurat per al període seleccionat.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $periodLabel = static::periodNumberOptions($period)[(string) $periodNumber] ?? $periodNumber;

                    $remittance = AssociatSepaRemittance::create([
                        'reference'          => AssociatSepaRemittance::nextReference($year),
                        'year'               => $year,
                        'execution_date'     => now()->addDays(5)->toDateString(),
                        'total_transactions' => $quotes->count(),
                        'total_amount'       => $quotes->sum('amount'),
                        'status'             => 'draft',
                        'notes'              => "{$periodLabel} {$year}",
                    ]);

                    $quotes->each(fn ($q) => $q->update(['remittance_id' => $remittance->id]));

                    $xml     = app(SepaXmlGenerator::class)->generate($remittance);
                    $xmlPath = "remeses-sepa/{$remittance->reference}.xml";
                    Storage::disk('local')->put($xmlPath, $xml);

                    $remittance->update([
                        'xml_path'     => $xmlPath,
                        'status'       => 'generated',
                        'generated_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Remesa generada')
                        ->body("{$remittance->reference} — {$quotes->count()} socis — {$remittance->total_amount} €")
                        ->success()
                        ->send();
                }),

            CreateAction::make()->label('Remesa manual'),
        ];
    }

    public static function periodNumberOptions(string $period): array
    {
        return match ($period) {
            'semi-annual' => [
                '1' => '1r semestre',
                '2' => '2n semestre',
            ],
            'quarterly' => [
                '1' => '1r trimestre',
                '2' => '2n trimestre',
                '3' => '3r trimestre',
                '4' => '4t trimestre',
            ],
            'monthly' => [
                '1'  => 'Gener',   '2'  => 'Febrer',  '3'  => 'Març',
                '4'  => 'Abril',   '5'  => 'Maig',     '6'  => 'Juny',
                '7'  => 'Juliol',  '8'  => 'Agost',    '9'  => 'Setembre',
                '10' => 'Octubre', '11' => 'Novembre', '12' => 'Desembre',
            ],
            default => ['1' => 'Anual'],
        };
    }
}
