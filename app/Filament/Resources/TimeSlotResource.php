<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeSlotResource\Pages;
use App\Models\CampusTimeSlot;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{Select, TextInput, TimePicker, Toggle};
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TimeSlotResource extends Resource
{
    protected static ?string $model = CampusTimeSlot::class;
    protected static ?int    $navigationSort = 13;

    public static function getNavigationIcon(): string   { return 'heroicon-o-clock'; }
    public static function getNavigationLabel(): string  { return __('site.timeslots'); }
    public static function getNavigationGroup(): string  { return __('site.catalog'); }
    public static function getModelLabel(): string       { return __('site.timeslot'); }
    public static function getPluralModelLabel(): string { return __('site.timeslots'); }

    public static function canAccess(): bool
    {
        $s = app(\App\Settings\SettingStore::class);
        return $s->get('campus_enabled', true)
            && $s->get('cataleg_enabled', true)
            && $s->get('cataleg_franges_enabled', true)
            && (auth()->user()?->hasPermissionTo('timeslots.view') ?? false);
    }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('timeslots.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('timeslots.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('timeslots.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('timeslots.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('site.timeslot'))->columns(2)->schema([
                Select::make('day_of_week')
                    ->label(__('site.timeslot_day'))
                    ->options(__('site.timeslot_days'))
                    ->required()->native(false),

                TextInput::make('code')
                    ->label(__('site.timeslot_code'))
                    ->required()->maxLength(10)
                    ->helperText('Ex: DL10, DC16, DJ18'),

                TimePicker::make('start_time')
                    ->label(__('site.timeslot_start'))
                    ->required()->seconds(false),

                TimePicker::make('end_time')
                    ->label(__('site.timeslot_end'))
                    ->required()->seconds(false)
                    ->after('start_time'),

                TextInput::make('description')
                    ->label(__('site.description'))
                    ->required()->maxLength(100)
                    ->columnSpanFull()
                    ->helperText('Ex: Dilluns 10:00–11:30'),

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
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label(__('site.timeslot_day'))
                    ->formatStateUsing(fn($state) => __('site.timeslot_days')[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('site.timeslot_code'))
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('start_time')
                    ->label(__('site.timeslot_start'))
                    ->formatStateUsing(fn($state) => substr($state, 0, 5)),

                Tables\Columns\TextColumn::make('end_time')
                    ->label(__('site.timeslot_end'))
                    ->formatStateUsing(fn($state) => substr($state, 0, 5)),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('site.description'))->searchable(),

                Tables\Columns\TextColumn::make('courses_count')
                    ->label(__('site.courses'))
                    ->counts('courses')->badge()->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('site.active'))->boolean(),
            ])
            ->defaultSort('day_of_week')
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
            'index'  => Pages\ListTimeSlots::route('/'),
            'create' => Pages\CreateTimeSlot::route('/create'),
            'edit'   => Pages\EditTimeSlot::route('/{record}/edit'),
        ];
    }
}
