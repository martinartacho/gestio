<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{DatePicker, Select, Textarea, TextInput, Toggle};
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = CampusCourse::class;
    protected static ?int    $navigationSort = 1;

    public static function getNavigationIcon(): string   { return 'heroicon-o-book-open'; }
    public static function getNavigationLabel(): string  { return __('site.courses'); }
    public static function getNavigationGroup(): string  { return __('site.courses_group'); }
    public static function getModelLabel(): string       { return __('site.course'); }
    public static function getPluralModelLabel(): string { return __('site.courses'); }

    public static function canAccess(): bool                                          { return auth()->user()?->hasPermissionTo('courses.view')   ?? false; }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('courses.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('courses.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('courses.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('courses.delete') ?? false; }

    // ── Formulari ──────────────────────────────────────────────────────────
    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // 1. Identificació
            Section::make(__('site.course_id_section'))->columns(2)->schema([
                TextInput::make('code')
                    ->label(__('site.course_code'))
                    ->maxLength(30)
                    ->helperText('Ex: SAN101, MON-DIGITAL'),

                TextInput::make('title')
                    ->label(__('site.course_title'))
                    ->required()->maxLength(200)->columnSpanFull(),

                Select::make('category_id')
                    ->label(__('site.category'))
                    ->relationship('category', 'name')
                    ->searchable()->preload()->native(false),

                Select::make('parent_id')
                    ->label(__('site.course_parent'))
                    ->helperText(__('site.course_parent_hint'))
                    ->options(
                        CampusCourse::whereNull('parent_id')
                            ->orderBy('title')
                            ->pluck('title', 'id')
                    )
                    ->searchable()->native(false)->nullable(),
            ]),

            // 2. Període i Planificació
            Section::make(__('site.course_planning'))->columns(2)->schema([
                Select::make('season_id')
                    ->label(__('site.season'))
                    ->relationship('season', 'name')
                    ->default(fn() => CampusSeason::where('is_active', true)->first()?->id)
                    ->required()->searchable()->preload()->native(false),

                Grid::make(2)->schema([
                    DatePicker::make('start_date')
                        ->label(__('site.course_start'))
                        ->native(false),

                    DatePicker::make('end_date')
                        ->label(__('site.course_end'))
                        ->native(false)->afterOrEqual('start_date'),
                ]),

                Textarea::make('calendar_notes')
                    ->label(__('site.course_calendar'))
                    ->helperText(__('site.course_calendar_hint'))
                    ->rows(2)->columnSpanFull(),

                TextInput::make('sessions')
                    ->label(__('site.course_sessions'))
                    ->numeric()->minValue(1),

                TextInput::make('hours')
                    ->label(__('site.course_hours'))
                    ->numeric()->minValue(1),
            ]),

            // 3. Espai i Horari
            Section::make(__('site.course_space_slot'))->columns(2)->schema([
                Select::make('space_id')
                    ->label(__('site.space'))
                    ->relationship('space', 'name')
                    ->searchable()->preload()->native(false)->nullable(),

                Select::make('time_slot_id')
                    ->label(__('site.timeslot'))
                    ->relationship('timeSlot', 'description')
                    ->searchable()->preload()->native(false)->nullable(),
            ]),

            // 4. Format i Places
            Section::make(__('site.course_format_pl'))->columns(2)->schema([
                Select::make('format')
                    ->label(__('site.course_format'))
                    ->options(__('site.formats'))
                    ->required()->native(false)->default('presencial'),

                TextInput::make('max_students')
                    ->label(__('site.course_max'))
                    ->helperText(__('site.course_max_hint'))
                    ->numeric()->minValue(1)->nullable(),

                TextInput::make('price')
                    ->label(__('site.course_price'))
                    ->numeric()->prefix('€')->default(0)->minValue(0),
            ]),

            // 5. Professors
            Section::make(__('site.course_teachers'))->schema([
                Select::make('teachers')
                    ->label(__('site.teachers'))
                    ->multiple()
                    ->relationship('teachers', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                    ->searchable()->preload(),
            ]),

            // 6. Contingut
            Section::make(__('site.course_content'))->schema([
                Textarea::make('description')
                    ->label(__('site.description'))
                    ->rows(3)->columnSpanFull(),

                Textarea::make('objectives')
                    ->label(__('site.course_objectives'))
                    ->rows(3)->columnSpanFull(),

                Textarea::make('requirements')
                    ->label(__('site.course_requirements'))
                    ->rows(2)->columnSpanFull(),
            ]),

            // 7. Configuració
            Section::make(__('site.course_config'))->columns(3)->schema([
                Select::make('status')
                    ->label(__('site.status'))
                    ->options(__('site.course_statuses'))
                    ->required()->native(false)->default('draft'),

                Toggle::make('is_active')
                    ->label(__('site.course_is_active'))
                    ->default(true)->inline(false),

                Toggle::make('is_public')
                    ->label(__('site.course_is_public'))
                    ->default(true)->inline(false),
            ]),
        ]);
    }

    // ── Taula ──────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('site.code'))
                    ->badge()->color('gray')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('site.course_title'))
                    ->searchable()->sortable()->weight('bold')
                    ->description(fn(CampusCourse $r) => $r->parent
                        ? '↳ ' . $r->parent->title
                        : ($r->children()->count() > 0
                            ? '📋 ' . __('site.template')
                            : null)),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('site.category'))
                    ->badge()
                    ->color(fn($record) => $record->category?->color ?? 'gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('season.name')
                    ->label(__('site.season'))
                    ->badge()->color('info')->sortable(),

                Tables\Columns\TextColumn::make('format')
                    ->label(__('site.course_format'))
                    ->formatStateUsing(fn($state) => __('site.formats')[$state] ?? $state)
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'presencial'     => 'success',
                        'online'         => 'info',
                        'semipresencial' => 'warning',
                        'hibrid'         => 'purple',
                        default          => 'gray',
                    }),

                Tables\Columns\TextColumn::make('max_students')
                    ->label(__('site.course_max'))
                    ->formatStateUsing(fn($state) => $state ?? '∞')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('site.course_price'))
                    ->money('EUR')->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('site.status'))
                    ->formatStateUsing(fn($state) => __('site.course_statuses')[$state] ?? $state)
                    ->badge()
                    ->color(fn($state) => CampusCourse::STATUS_COLORS[$state] ?? 'gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('site.active'))->boolean()
                    ->trueColor('success')->falseColor('danger'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('site.course_start'))
                    ->date('d/m/Y')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('title')
            ->recordAction(null)
            ->filters([
                Tables\Filters\SelectFilter::make('season_id')
                    ->label(__('site.season'))
                    ->relationship('season', 'name')
                    ->native(false),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('site.category'))
                    ->relationship('category', 'name')
                    ->native(false),

                Tables\Filters\SelectFilter::make('format')
                    ->label(__('site.course_format'))
                    ->options(__('site.formats'))
                    ->native(false),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options(__('site.course_statuses'))
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('site.active'))
                    ->trueLabel(__('site.only_active'))
                    ->falseLabel(__('site.only_inactive'))
                    ->native(false),
            ])
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

    public static function getNavigationBadge(): ?string
    {
        return (string) CampusCourse::where('is_active', true)->count();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit'   => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
