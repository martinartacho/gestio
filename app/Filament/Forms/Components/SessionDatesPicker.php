<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

/** Calendari on es marquen els dies de cada sessió del curs. L'estat és una llista de dates Y-m-d. */
class SessionDatesPicker extends Field
{
    protected string $view = 'filament.forms.components.session-dates-picker';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);
    }
}
