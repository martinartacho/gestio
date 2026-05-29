<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssociatMemberResource\Pages;
use App\Models\AssociatMember;
use App\Settings\SettingStore;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AssociatMemberResource extends Resource
{
    protected static ?string $model = AssociatMember::class;
    protected static ?int    $navigationSort = 5;

    public static function getNavigationIcon(): string   { return 'heroicon-o-identification'; }
    public static function getNavigationGroup(): string  { return 'Associats'; }
    public static function getNavigationLabel(): string  { return 'Socis'; }
    public static function getModelLabel(): string       { return 'soci'; }
    public static function getPluralModelLabel(): string { return 'socis'; }

    public static function canAccess(): bool
    {
        return (bool) app(SettingStore::class)->get('associats_enabled', false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Identificació')->schema([
                TextInput::make('member_number')
                    ->label('Número de soci')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),

                TextInput::make('first_name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(100),

                TextInput::make('last_name')
                    ->label('Cognoms')
                    ->required()
                    ->maxLength(100),

                Select::make('status')
                    ->label('Estat')
                    ->options([
                        'active'    => 'Actiu',
                        'pending'   => 'Pendent',
                        'cancelled' => 'Baixa',
                    ])
                    ->required()
                    ->default('pending'),
            ])->columns(2),

            Section::make('Contacte')->schema([
                TextInput::make('email')
                    ->label('Correu electrònic')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('phone')
                    ->label('Telèfon')
                    ->tel()
                    ->maxLength(20),

                TextInput::make('address')
                    ->label('Adreça')
                    ->maxLength(255),

                TextInput::make('postal_code')
                    ->label('Codi postal')
                    ->maxLength(10),

                TextInput::make('city')
                    ->label('Ciutat')
                    ->maxLength(100),
            ])->columns(2),

            Section::make('Dates')->schema([
                DatePicker::make('joined_at')
                    ->label('Data d\'alta')
                    ->displayFormat('d/m/Y'),

                DatePicker::make('cancelled_at')
                    ->label('Data de baixa')
                    ->displayFormat('d/m/Y'),
            ])->columns(2),

            Section::make('Accés')->schema([
                TextInput::make('password')
                    ->label('Contrasenya')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context) => $context === 'create'),

                Toggle::make('data_consent')
                    ->label('Consentiment de dades (RGPD)')
                    ->default(false),
            ])->columns(2),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_number')
                    ->label('Nº soci')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label('Cognoms')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correu')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estat')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger'  => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'active'    => 'Actiu',
                        'pending'   => 'Pendent',
                        'cancelled' => 'Baixa',
                        default     => $state,
                    }),

                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Alta')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estat')
                    ->options([
                        'active'    => 'Actiu',
                        'pending'   => 'Pendent',
                        'cancelled' => 'Baixa',
                    ]),
            ])
            ->defaultSort('member_number');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssociatMembers::route('/'),
            'create' => Pages\CreateAssociatMember::route('/create'),
            'edit'   => Pages\EditAssociatMember::route('/{record}/edit'),
        ];
    }
}
