<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Tenant;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationLabel(): string
    {
        return __('site.users');
    }

    public static function getNavigationGroup(): string
    {
        return __('site.administration') ?? 'Administració';
    }

    public static function getModelLabel(): string
    {
        return __('site.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.users');
    }

    public static function canAccess(): bool
    {
        $s = app(\App\Settings\SettingStore::class);
        return $s->get('gestio_enabled', true)
            && $s->get('gestio_administracio_enabled', true)
            && (auth()->user()?->hasPermissionTo('users.view') ?? false);
    }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('users.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('users.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('users.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('users.delete') ?? false; }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        if (! auth()->user()?->hasRole('super-admin')) {
            $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super-admin'));
        }
        return $query;
    }

    // ── Formulari ──────────────────────────────────────────────────────────
    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('site.personal_info'))
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('site.full_name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label(__('site.email'))
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                ]),

            Section::make(__('site.password'))
                ->columns(2)
                ->description(__('site.password_hint'))
                ->schema([
                    TextInput::make('password')
                        ->label(__('site.new_password'))
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn(string $operation) => $operation === 'create')
                        ->maxLength(255),

                    TextInput::make('password_confirmation')
                        ->label(__('site.confirm_password'))
                        ->password()
                        ->revealable()
                        ->same('password')
                        ->required(fn(string $operation) => $operation === 'create')
                        ->dehydrated(false),
                ]),

            Section::make(__('site.access_and_roles'))
                ->columns(2)
                ->schema([
                    Select::make('roles')
                        ->label(__('site.roles'))
                        ->multiple()
                        ->relationship('roles', 'name')
                        ->getOptionLabelFromRecordUsing(fn($record) => ucfirst($record->name))
                        ->preload()
                        ->searchable()
                        ->required(),

                    Toggle::make('active')
                        ->label(__('site.active_user'))
                        ->helperText(__('site.active_user_hint'))
                        ->default(true)
                        ->inline(false),

                    // Nomes super-admin: un admin normal ja crea l'usuari dins
                    // de la seva pròpia institució (Filament ho fa sol); això
                    // és per poder assignar-lo/corregir-lo a una altra.
                    Select::make('tenant_id')
                        ->label('Institució')
                        ->options(fn () => Tenant::pluck('name', 'id'))
                        ->searchable()
                        ->helperText('Deixa-ho buit per a un super-admin (veu totes les institucions).')
                        ->visible(fn () => auth()->user()?->hasRole('super-admin') ?? false)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    // ── Taula ──────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('site.full_name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn(User $record) => $record->email),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('site.roles'))
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->color('primary')
                    ->separator(','),

                Tables\Columns\IconColumn::make('active')
                    ->label(__('site.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordAction(null)
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label(__('site.role'))
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => ucfirst($record->name))
                    ->preload(),

                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('site.status'))
                    ->trueLabel(__('site.only_active'))
                    ->falseLabel(__('site.only_inactive'))
                    ->native(false),
            ])
            ->actions([
                EditAction::make()
                    ->label(__('site.edit')),

                Action::make('toggleActive')
                    ->label(fn(User $record) => $record->active
                        ? __('site.deactivate')
                        : __('site.activate'))
                    ->icon(fn(User $record) => $record->active
                        ? 'heroicon-o-x-circle'
                        : 'heroicon-o-check-circle')
                    ->color(fn(User $record) => $record->active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['active' => ! $record->active]);
                        Notification::make()
                            ->title($record->active
                                ? __('site.user_activated')
                                : __('site.user_deactivated'))
                            ->success()
                            ->send();
                    })
                    ->hidden(fn(User $record) => $record->id === auth()->id()),

                DeleteAction::make()
                    ->label(__('site.delete'))
                    ->hidden(fn(User $record) => $record->id === auth()->id()),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) User::where('tenant_id', current_tenant()?->id)->count();
    }
}