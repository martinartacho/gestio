<?php

namespace App\Facades;

use App\Settings\SettingStore;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed  get(string $key, mixed $default = null)
 * @method static array  all()
 * @method static bool   has(string $key)
 * @method static void   set(string $key, mixed $value)
 * @method static void   setMany(array $settings)
 * @method static void   flush()
 * @method static void   reload()
 *
 * @see SettingStore
 */
class Setting extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingStore::class;
    }
}
