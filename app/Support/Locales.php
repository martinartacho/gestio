<?php

declare(strict_types=1);

namespace App\Support;

final class Locales
{
    public const SUPPORTED = [
        'ca' => 'Català',
        'es' => 'Castellano',
    ];

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && array_key_exists($locale, self::SUPPORTED);
    }

    public static function codes(): array
    {
        return array_keys(self::SUPPORTED);
    }

    public static function rule(): array
    {
        return ['nullable', 'string', 'in:'.implode(',', self::codes())];
    }
}
