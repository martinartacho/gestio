<?php

namespace App\Filament\Resources\CampusStudents\Schemas;

use App\Filament\Support\PasswordField;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CampusStudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Dades de l\'alumne'))->columns(2)->schema([
                    TextInput::make('first_name')
                        ->label(__('Nom'))
                        ->required()->maxLength(100),

                    TextInput::make('last_name')
                        ->label(__('Cognoms'))
                        ->required()->maxLength(100),

                    // Únic globalment (la persona, no el compte, és d'una institució o una altra).
                    TextInput::make('email')
                        ->label(__('Correu electrònic'))
                        ->email()->required()->maxLength(150)
                        ->unique(ignoreRecord: true),

                    TextInput::make('phone')
                        ->label(__('Telèfon'))
                        ->tel()->maxLength(20),
                ]),

                Section::make(__('Accés'))->columns(2)
                    ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'manager']))
                    ->schema([
                        PasswordField::make('student_password', requiredOnCreate: true),
                    ]),
            ]);
    }
}
