<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Actions\SeedTenantSampleData;
use App\Filament\Resources\TenantResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /** Comptadors de dades d'exemple, guardats a part perquè Tenant no els té com a columnes. */
    private array $sampleCounts = [];

    /** Dades de l'admin de l'entitat, mateix motiu. */
    private ?array $adminData = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->sampleCounts = [
            'news'     => (int) ($data['sample_news'] ?? 0),
            'teachers' => (int) ($data['sample_teachers'] ?? 0),
            'courses'  => (int) ($data['sample_courses'] ?? 0),
            'students' => (int) ($data['sample_students'] ?? 0),
            'members'  => (int) ($data['sample_members'] ?? 0),
        ];

        if (filled($data['admin_email'] ?? null)) {
            $this->adminData = [
                'name'     => $data['admin_name'] ?: $data['admin_email'],
                'email'    => $data['admin_email'],
                'password' => $data['admin_password'],
            ];
        }

        unset(
            $data['sample_news'], $data['sample_teachers'], $data['sample_courses'],
            $data['sample_students'], $data['sample_members'],
            $data['admin_name'], $data['admin_email'], $data['admin_password'],
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->adminData) {
            $admin = User::create([
                'name'     => $this->adminData['name'],
                'email'    => $this->adminData['email'],
                'password' => bcrypt($this->adminData['password']),
                'active'   => true,
                'tenant_id'=> $this->record->id,
            ]);
            $admin->syncRoles(['admin']);

            Notification::make()
                ->title("Administrador creat: {$admin->email}")
                ->success()
                ->send();
        }

        if (array_sum($this->sampleCounts) > 0) {
            (new SeedTenantSampleData())->run($this->record, $this->sampleCounts);

            Notification::make()
                ->title('Dades d\'exemple generades')
                ->body(collect($this->sampleCounts)->filter()->map(fn ($n, $k) => "{$n} {$k}")->implode(', '))
                ->success()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
