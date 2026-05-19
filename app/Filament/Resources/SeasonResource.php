<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeasonResource\Pages;
use App\Models\CampusSeason;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SeasonResource extends Resource
{
    protected static ?string $model = CampusSeason::class;
    protected static ?int    $navigationSort = 10;

    public static function getNavigationIcon(): string   { return 'heroicon-o-calendar'; }
    public static function getNavigationLabel(): string  { return __('site.seasons'); }
    public static function getNavigationGroup(): string  { return __('site.catalog'); }
    public static function getModelLabel(): string       { return __('site.season'); }
    public static function getPluralModelLabel(): string { return __('site.seasons'); }

    public static function canAccess(): bool                                          { return auth()->user()?->hasPermissionTo('seasons.view')   ?? false; }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('seasons.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('seasons.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('seasons.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('seasons.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('site.season'))->columns(2)->schema([
                TextInput::make('name')
                    ->label(__('site.season_name'))
                    ->required()->maxLength(100),

                TextInput::make('year')
                    ->label(__('site.season_year'))
                    ->numeric()->required()
                    ->minValue(2020)->maxValue(2050),

                TextInput::make('quadrimester')
                    ->label(__('site.season_period'))
                    ->numeric()->required()
                    ->minValue(1)->maxValue(9)
                    ->rules([
                        fn(\Filament\Schemas\Components\Utilities\Get $get, $record) => \Illuminate\Validation\Rule::unique('campus_seasons', 'quadrimester')
                            ->where('year', $get('year'))
                            ->ignore($record?->id),
                    ]),

                Toggle::make('is_active')
                    ->label(__('site.season_active'))
                    ->helperText(__('site.season_active_hint'))
                    ->inline(false),
            ]),

            Section::make(__('site.season_dates'))->columns(2)->schema([
                DatePicker::make('start_date')
                    ->label(__('site.season_start'))
                    ->required()->native(false),

                DatePicker::make('end_date')
                    ->label(__('site.season_end'))
                    ->required()->native(false)
                    ->afterOrEqual('start_date'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('site.season_name'))
                    ->searchable()->sortable()->weight('bold'),

                Tables\Columns\TextColumn::make('quadrimester')
                    ->label(__('site.season_period'))
                    ->formatStateUsing(fn($state) => "P{$state}")
                    ->badge()->color('info'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('site.season_start'))
                    ->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label(__('site.season_end'))
                    ->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('courses_count')
                    ->label(__('site.courses'))
                    ->counts('courses')->badge()->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('site.active'))
                    ->boolean()->trueColor('success')->falseColor('gray'),
            ])
            ->defaultSort('year', 'desc')
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
            'index'  => Pages\ListSeasons::route('/'),
            'create' => Pages\CreateSeason::route('/create'),
            'edit'   => Pages\EditSeason::route('/{record}/edit'),
        ];
    }
}
