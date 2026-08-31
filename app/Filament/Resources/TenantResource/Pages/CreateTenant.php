<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Actions\SeedTenantSampleData;
use App\Filament\Resources\TenantResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /** Comptadors de dades d'exemple, guardats a part perquè Tenant no els té com a columnes. */
    private array $sampleCounts = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->sampleCounts = [
            'news'     => (int) ($data['sample_news'] ?? 0),
            'teachers' => (int) ($data['sample_teachers'] ?? 0),
            'courses'  => (int) ($data['sample_courses'] ?? 0),
            'students' => (int) ($data['sample_students'] ?? 0),
            'members'  => (int) ($data['sample_members'] ?? 0),
        ];

        unset($data['sample_news'], $data['sample_teachers'], $data['sample_courses'], $data['sample_students'], $data['sample_members']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (array_sum($this->sampleCounts) === 0) {
            return;
        }

        (new SeedTenantSampleData())->run($this->record, $this->sampleCounts);

        Notification::make()
            ->title('Dades d\'exemple generades')
            ->body(collect($this->sampleCounts)->filter()->map(fn ($n, $k) => "{$n} {$k}")->implode(', '))
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
