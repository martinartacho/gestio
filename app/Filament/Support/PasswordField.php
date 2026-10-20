<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;

/**
 * Camp «Contrasenya» dels formularis d'administració (professors i alumnes).
 *
 * - En alta, el camp surt ja omplert amb la contrasenya per defecte del grup (SEEDER_*_PASSWORD del .env), però només
 *   per a admin i manager: no cal escriure-la i es pot canviar. Per a qui no té aquests rols surt buit.
 * - En edició surt buit: si no s'escriu res, no es toca la contrasenya. Un botó dins del camp hi posa la per defecte.
 * - El model la xifra sol (cast `hashed`); aquí mai es guarda en text pla ni es fa Hash::make a mà.
 */
final class PasswordField
{
    /** @param  string  $configKey  clau de config/seeder.php amb la contrasenya per defecte del grup (p. ex. «teacher_password») */
    public static function make(string $configKey, bool $requiredOnCreate = false): TextInput
    {
        return TextInput::make('password')
            ->label(__('Contrasenya'))
            ->password()
            ->revealable()
            ->autocomplete('new-password')
            ->minLength(8)
            ->maxLength(255)
            ->required(fn (string $operation): bool => $requiredOnCreate && $operation === 'create')
            // Buit en edició = no canviar-la.
            ->dehydrated(fn (?string $state): bool => filled($state))
            ->default(fn (string $operation): ?string => $operation === 'create' && self::canUseDefault($configKey) ? self::defaultPassword($configKey) : null)
            ->helperText(fn (string $operation): string => $operation === 'create'
                ? __('Mínim 8 caràcters. Pots deixar la per defecte o escriure\'n una altra.')
                : __('Deixa-ho buit per no canviar-la. Mínim 8 caràcters.'))
            ->suffixAction(
                Action::make('useDefaultPassword')
                    ->icon('heroicon-o-key')
                    ->tooltip(__('Posar la contrasenya per defecte'))
                    ->visible(fn (): bool => self::canUseDefault($configKey))
                    ->action(fn (Set $set) => $set('password', self::defaultPassword($configKey))),
            );
    }

    /** La contrasenya per defecte (del .env), o null si no està definida. */
    public static function defaultPassword(string $configKey): ?string
    {
        $value = config("seeder.{$configKey}");

        return is_string($value) && $value !== '' ? $value : null;
    }

    /** Només admin i manager poden veure i fer servir la contrasenya per defecte, i només si està definida al .env. */
    public static function canUseDefault(string $configKey): bool
    {
        return self::defaultPassword($configKey) !== null
            && (bool) auth()->user()?->hasAnyRole(['admin', 'manager']);
    }
}
