<?php

namespace App\Filament\Resources\AssociatSepaRemittanceResource\Pages;

use App\Filament\Resources\AssociatSepaRemittanceResource;
use App\Services\SepaXmlGenerator;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EditAssociatSepaRemittance extends EditRecord
{
    protected static string $resource = AssociatSepaRemittanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_xml')
                ->label('Descarregar pain.008.xml')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => $this->record->xml_path && Storage::disk('local')->exists($this->record->xml_path))
                ->action(function (): StreamedResponse {
                    return Storage::disk('local')->download(
                        $this->record->xml_path,
                        "{$this->record->reference}.xml",
                        ['Content-Type' => 'application/xml']
                    );
                }),

            Action::make('regenerate_xml')
                ->label('Regenerar XML')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->isDraft() || $this->record->status === 'generated')
                ->action(function (): void {
                    $this->record->load('quotes.member');
                    $xml     = app(SepaXmlGenerator::class)->generate($this->record);
                    $xmlPath = "remeses-sepa/{$this->record->reference}.xml";
                    Storage::disk('local')->put($xmlPath, $xml);

                    $this->record->update([
                        'xml_path'     => $xmlPath,
                        'status'       => 'generated',
                        'generated_at' => now(),
                    ]);

                    Notification::make()->title('XML regenerat')->success()->send();
                }),

            Action::make('rename_reference')
                ->label('Reanomenar')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->modalHeading('Canviar la referència de la remesa')
                ->form([
                    TextInput::make('reference')
                        ->label('Nova referència')
                        ->default(fn () => $this->record->reference)
                        ->required()
                        ->maxLength(35)
                        ->unique(
                            table: 'associat_sepa_remittances',
                            column: 'reference',
                            ignorable: fn () => $this->record,
                        ),
                ])
                ->action(function (array $data): void {
                    $newRef  = $data['reference'];
                    $updates = ['reference' => $newRef];

                    if ($this->record->xml_path && Storage::disk('local')->exists($this->record->xml_path)) {
                        $newXmlPath = "remeses-sepa/{$newRef}.xml";
                        Storage::disk('local')->move($this->record->xml_path, $newXmlPath);
                        $updates['xml_path'] = $newXmlPath;
                    }

                    $this->record->update($updates);
                    $this->refreshFormData(['reference', 'xml_path']);

                    Notification::make()->title('Referència actualitzada')->success()->send();
                }),

            DeleteAction::make()
                ->visible(fn () => $this->record->isDraft()),
        ];
    }
}
