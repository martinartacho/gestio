<?php

declare(strict_types=1);

namespace App\Filament\Auth;

use App\Support\Locales;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

// Fora de app/Filament/Pages perquè discoverPages() no la registri com a pàgina normal.
class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                Select::make('locale')
                    ->label(__('site.language'))
                    ->options(Locales::SUPPORTED)
                    ->placeholder(__('site.language_tenant_default')),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    // Recarregar perquè el nou idioma s'apliqui a tot el panell.
    protected function afterSave(): void
    {
        $this->redirect(static::getUrl());
    }
}
