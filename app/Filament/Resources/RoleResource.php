<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shield-check';
    }

    public static function getNavigationLabel(): string
    {
        return __('site.roles');
    }

    public static function getNavigationGroup(): string
    {
        return __('site.administration') ?? 'Administració';
    }

    public static function getModelLabel(): string
    {
        return __('site.role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.roles');
    }

    // ── Formulari ──────────────────────────────────────────────────────────
    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('site.role_data'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('site.role_name'))
                        ->required()
                        ->unique(table: 'roles', column: 'name', ignoreRecord: true)
                        ->maxLength(100)
                        ->helperText(__('site.role_name_hint')),
                ]),

            Section::make(__('site.permissions'))
                ->description(__('site.permissions_description'))
                ->schema([
                    CheckboxList::make('permissions')
                        ->label('')
                        ->relationship('permissions', 'name')
                        ->getOptionLabelFromRecordUsing(
                            fn($record) => ucfirst(str_replace('.', ' → ', $record->name))
                        )
                        ->searchable()
                        ->bulkToggleable()
                        ->columns(2),
                ]),
        ]);
    }

    // ── Taula ──────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('site.role'))
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label(__('site.permissions'))
                    ->counts('permissions')
                    ->badge()
                    ->color('warning')
                    ->suffix(' permisos'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label(__('site.users'))
                    ->counts('users')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'primary' : 'gray')
                    ->suffix(' usuaris'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordAction(null)
            ->actions([
                EditAction::make()
                    ->label(__('site.edit')),

                DeleteAction::make()
                    ->label(__('site.delete'))
                    ->before(function (Role $record, DeleteAction $action) {
                        if ($record->users()->count() > 0) {
                            $action->cancel();
                            Notification::make()
                                ->title(__('site.role_cannot_delete'))
                                ->body(__('site.role_has_users', ['role' => $record->name]))
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('site.delete_selected')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}