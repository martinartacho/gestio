<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{TextInput, Toggle};
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TenantResource extends Resource
{
    // Un tenant no pertany a cap tenant — mai s'ha de filtrar per si mateix.
    protected static bool $isScopedToTenant = false;

    protected static ?string $model = Tenant::class;
    protected static ?int    $navigationSort = 0;

    public static function getNavigationIcon(): string   { return 'heroicon-o-building-office-2'; }
    public static function getNavigationLabel(): string  { return 'Entitats'; }
    public static function getModelLabel(): string       { return 'Entitat'; }
    public static function getPluralModelLabel(): string { return 'Entitats'; }

    // Nomes el super-admin gestiona les entitats/tenants.
    public static function canAccess(): bool                              { return auth()->user()?->hasRole('super-admin') ?? false; }
    public static function canCreate(): bool                              { return auth()->user()?->hasRole('super-admin') ?? false; }
    public static function canEdit(Model $record): bool                   { return auth()->user()?->hasRole('super-admin') ?? false; }
    public static function canDelete(Model $record): bool                 { return auth()->user()?->hasRole('super-admin') ?? false; }
    public static function canDeleteAny(): bool                           { return auth()->user()?->hasRole('super-admin') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Entitat')->columns(2)->schema([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $state, callable $set, string $operation) =>
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->label('Slug (identifica la URL, p. ex. /admin/{slug})')
                    ->required()->maxLength(50)
                    ->alphaDash()->unique(ignoreRecord: true)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true)->inline(false),
            ]),

            Section::make('Administrador de l\'entitat')
                ->description('Opcional, només en crear. Crea un usuari amb rol \'admin\' per a aquesta institució.')
                ->visibleOn('create')
                ->columns(3)
                ->schema([
                    TextInput::make('admin_name')
                        ->label('Nom')
                        ->maxLength(255),

                    TextInput::make('admin_email')
                        ->label('Email')
                        ->email()
                        ->unique(table: 'users', column: 'email')
                        ->requiredWith('admin_name')
                        ->maxLength(255),

                    TextInput::make('admin_password')
                        ->label('Contrasenya')
                        ->password()->revealable()
                        ->requiredWith('admin_email')
                        ->minLength(8),
                ]),

            Section::make('Dades d\'exemple')
                ->description('Opcional, només en crear. Genera contingut fictici perquè l\'entitat no comenci buida.')
                ->visibleOn('create')
                ->schema([
                    Grid::make(5)->schema([
                        TextInput::make('sample_news')->label('Notícies')->numeric()->default(0)->minValue(0)->maxValue(50),
                        TextInput::make('sample_teachers')->label('Professorat')->numeric()->default(0)->minValue(0)->maxValue(50),
                        TextInput::make('sample_courses')->label('Cursos')->numeric()->default(0)->minValue(0)->maxValue(50),
                        TextInput::make('sample_students')->label('Alumnes')->numeric()->default(0)->minValue(0)->maxValue(200),
                        TextInput::make('sample_members')->label('Socis')->numeric()->default(0)->minValue(0)->maxValue(200),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Usuaris')
                    ->counts('users')->badge()->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('name')
            ->recordAction(null)
            ->actions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Eliminar'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar seleccionades'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit'   => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
