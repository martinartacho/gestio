<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpaceResource\Pages;
use App\Models\CampusSpace;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{Select, TextInput, Textarea, Toggle};
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SpaceResource extends Resource
{
    protected static ?string $model = CampusSpace::class;
    protected static ?int    $navigationSort = 12;

    public static function getNavigationIcon(): string   { return 'heroicon-o-building-office'; }
    public static function getNavigationLabel(): string  { return __('site.spaces'); }
    public static function getNavigationGroup(): string  { return __('site.catalog'); }
    public static function getModelLabel(): string       { return __('site.space'); }
    public static function getPluralModelLabel(): string { return __('site.spaces'); }

    public static function canAccess(): bool                                          { return auth()->user()?->hasPermissionTo('spaces.view')   ?? false; }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('spaces.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('spaces.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('spaces.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('spaces.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('site.space'))->columns(2)->schema([
                TextInput::make('name')
                    ->label(__('site.name'))
                    ->required()->maxLength(100),

                TextInput::make('code')
                    ->label(__('site.space_code'))
                    ->required()->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->helperText('Ex: SA, AM1, ONLINE'),

                Select::make('type')
                    ->label(__('site.space_type'))
                    ->options(__('site.space_types'))
                    ->required()->native(false),

                TextInput::make('capacity')
                    ->label(__('site.space_capacity'))
                    ->numeric()->default(0)
                    ->helperText('0 = sense límit (ex: espais virtuals)'),

                Textarea::make('description')
                    ->label(__('site.description'))
                    ->rows(2)->columnSpanFull(),

                Toggle::make('is_active')
                    ->label(__('site.active'))
                    ->default(true)->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('site.space_code'))
                    ->badge()->color('gray')->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('site.name'))
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('site.type'))
                    ->formatStateUsing(fn($state) => __('site.space_types')[$state] ?? $state)
                    ->badge()->color('info'),

                Tables\Columns\TextColumn::make('capacity')
                    ->label(__('site.space_capacity'))
                    ->formatStateUsing(fn($state) => $state > 0 ? $state : '∞')
                    ->sortable(),

                Tables\Columns\TextColumn::make('courses_count')
                    ->label(__('site.courses'))
                    ->counts('courses')->badge()->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('site.active'))->boolean(),
            ])
            ->defaultSort('name')
            ->recordAction(null)
            ->actions([
                EditAction::make()->label(__('site.edit')),
                DeleteAction::make()->label(__('site.delete')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('site.delete_selected')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSpaces::route('/'),
            'create' => Pages\CreateSpace::route('/create'),
            'edit'   => Pages\EditSpace::route('/{record}/edit'),
        ];
    }
}
