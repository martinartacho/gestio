<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Models\CampusHoliday;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{DatePicker, Select, TextInput, Toggle};
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HolidayResource extends Resource
{
    protected static ?string $model = CampusHoliday::class;
    protected static ?int    $navigationSort = 14;

    public static function getNavigationIcon(): string   { return 'heroicon-o-calendar-date-range'; }
    public static function getNavigationLabel(): string  { return __('site.holidays'); }
    public static function getNavigationGroup(): string  { return __('site.catalog'); }
    public static function getModelLabel(): string       { return __('site.holiday'); }
    public static function getPluralModelLabel(): string { return __('site.holidays'); }

    public static function canAccess(): bool
    {
        $s = app(\App\Settings\SettingStore::class);
        return $s->getRaw('campus_enabled', true)
            && (auth()->user()?->hasPermissionTo('holidays.view') ?? false);
    }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('holidays.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('holidays.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('holidays.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('holidays.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('site.holiday'))->columns(2)->schema([
                DatePicker::make('date')
                    ->label(__('site.holiday_date'))
                    ->required()->native(false)->displayFormat('d/m/Y'),

                DatePicker::make('date_end')
                    ->label(__('site.holiday_date_end'))
                    ->helperText(__('site.holiday_date_end_hint'))
                    ->native(false)->displayFormat('d/m/Y')
                    ->afterOrEqual('date'),

                TextInput::make('label')
                    ->label(__('site.holiday_label'))
                    ->required()->maxLength(100)
                    ->helperText('Ex: Nadal, Festa Major de Granollers'),

                Select::make('type')
                    ->label(__('site.holiday_type'))
                    ->options(CampusHoliday::TYPES)
                    ->required()->native(false)->default('festiu'),

                Toggle::make('recurring_yearly')
                    ->label(__('site.holiday_recurring'))
                    ->helperText(__('site.holiday_recurring_hint'))
                    ->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label(__('site.holiday_date'))
                    ->formatStateUsing(fn($record) => $record->date_end && ! $record->date_end->eq($record->date)
                        ? $record->date->format('d/m/Y') . ' – ' . $record->date_end->format('d/m/Y')
                        : $record->date->format('d/m/Y'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('label')
                    ->label(__('site.holiday_label'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('site.holiday_type'))
                    ->formatStateUsing(fn($state) => CampusHoliday::TYPES[$state] ?? $state)
                    ->badge()
                    ->color(fn($state) => $state === 'festiu' ? 'danger' : 'warning'),

                Tables\Columns\IconColumn::make('recurring_yearly')
                    ->label(__('site.holiday_recurring'))->boolean(),
            ])
            ->defaultSort('date')
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
            'index'  => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit'   => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
