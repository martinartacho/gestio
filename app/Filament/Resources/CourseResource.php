<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use Filament\Actions\{ActionGroup, BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction, Action};
use Filament\Forms\Components\{DatePicker, Select, Textarea, TextInput, Toggle};
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
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

    public static function canAccess(): bool
    {
        $s = app(\App\Settings\SettingStore::class);
        return $s->get('campus_enabled', true)
            && $s->get('campus_cursos_enabled', true)
            && (auth()->user()?->hasPermissionTo('courses.view') ?? false);
    }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('courses.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('courses.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('courses.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('courses.delete') ?? false; }

    // ── Formulari ──────────────────────────────────────────────────────────
    public static function form(Schema $schema): Schema
    {
        // Camps d'identitat bloquejats quan Actiu (tothom) o Tancat (no-admin)
        $lockIdentity = fn(?CampusCourse $record): bool =>
            $record !== null && (
                $record->status === 'active' ||
                ($record->status === 'closed' && ! auth()->user()?->hasRole('admin'))
            );

        // Tots els camps bloquejats quan Tancat (no-admin)
        $lockAll = fn(?CampusCourse $record): bool =>
            $record !== null
            && $record->status === 'closed'
            && ! auth()->user()?->hasRole('admin');

        // max_students: bloquejat quan Actiu+matriculats, o Tancat+no-admin
        $lockMaxStudents = fn(?CampusCourse $record): bool =>
            $record !== null && (
                ($record->status === 'active' && $record->enrollments()->exists()) ||
                ($record->status === 'closed' && ! auth()->user()?->hasRole('admin'))
            );

        return $schema->components([

            // 1. Identificació
            Section::make(__('site.course_id_section'))
                ->columns(2)
                ->description(function (?CampusCourse $record): ?string {
                    if (! $record) return null;
                    return match ($record->status) {
                        'active' => '⚠️ Curs actiu — títol, codi, temporada, preu i format estan bloquejats. Només admin pot desbloquejar',
                        'closed' => auth()->user()?->hasRole('admin')
                            ? '🔓 Curs tancat — edició excepcional (admin).'
                            : '🔒 Curs tancat — tots els camps estan bloquejats.',
                        default  => null,
                    };
                })
                ->schema([
                TextInput::make('title')
                    ->label(__('site.course_title'))
                    ->required()->maxLength(200)->columnSpanFull()
                    ->disabled($lockIdentity)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $state, Set $set, Get $get): void {
                        if ($get('code')) return;
                        $stop  = ['de', 'del', 'la', 'el', 'els', 'les', 'per', 'amb', 'una', 'uns', 'un', 'i', 'a', 'en', 'o'];
                        $words = array_values(array_filter(
                            explode(' ', Str::ascii(trim($state))),
                            fn($w) => strlen($w) > 2 && ! in_array(strtolower($w), $stop)
                        ));
                        $code = strtoupper(implode('', array_map(fn($w) => substr($w, 0, 3), array_slice($words, 0, 2))));
                        if ($code) {
                            $set('code', $code);
                        }
                    }),

                TextInput::make('code')
                    ->label(__('site.course_code'))
                    ->maxLength(30)
                    ->helperText('Ex: SAN101, MON-DIGITAL. Auto-generat des del títol si s\'omite.')
                    ->placeholder('Auto-generat')
                    ->disabled($lockIdentity),

                Select::make('category_id')
                    ->label(__('site.category'))
                    ->relationship('category', 'name')
                    ->searchable()->preload()->native(false)
                    ->disabled($lockAll),

                Select::make('parent_id')
                    ->label(__('site.course_parent'))
                    ->helperText(__('site.course_parent_hint'))
                    ->options(
                        CampusCourse::whereNull('parent_id')
                            ->orderBy('title')
                            ->pluck('title', 'id')
                    )
                    ->searchable()->native(false)->nullable()
                    ->disabled($lockAll),
            ]),

            // 2. Període i Planificació
            Section::make(__('site.course_planning'))->columns(2)->schema([
                Select::make('season_id')
                    ->label(__('site.season'))
                    ->relationship('season', 'name')
                    ->default(fn() => CampusSeason::where('status', 'draft')->first()?->id)
                    ->required()->searchable()->preload()->native(false)
                    ->disabled($lockIdentity)
                    ->live()
                    ->hint(function (Get $get): ?string {
                        $season = CampusSeason::find($get('season_id'));
                        if (! $season || $season->status === 'draft') return null;
                        return 'Atenció: "' . $season->name . '" és ' . (CampusSeason::STATUSES[$season->status] ?? $season->status);
                    })
                    ->hintColor('warning')
                    ->afterStateUpdated(function ($state, Set $set): void {
                        $season = $state ? CampusSeason::find($state) : null;
                        if (! $season) return;
                        $set('start_date', $season->start_date?->format('Y-m-d'));
                        $set('end_date',   $season->end_date?->format('Y-m-d'));
                    }),

                Grid::make(2)->schema([
                    DatePicker::make('start_date')
                        ->label(__('site.course_start'))
                        ->native(false)
                        ->default(fn() => CampusSeason::where('status', 'draft')->first()?->start_date?->format('Y-m-d'))
                        ->disabled($lockAll),

                    DatePicker::make('end_date')
                        ->label(__('site.course_end'))
                        ->native(false)->afterOrEqual('start_date')
                        ->default(fn() => CampusSeason::where('status', 'draft')->first()?->end_date?->format('Y-m-d'))
                        ->disabled($lockAll),
                ]),

                Textarea::make('calendar_notes')
                    ->label(__('site.course_calendar'))
                    ->helperText(__('site.course_calendar_hint'))
                    ->rows(2)->columnSpanFull()
                    ->live(onBlur: true)
                    ->disabled($lockAll)
                    ->hint(function (Get $get): ?string {
                        $notes  = $get('calendar_notes');
                        $endRaw = $get('end_date');
                        if (! $notes || ! $endRaw) return null;

                        // Suport tant Carbon com string Y-m-d
                        $endCarbon = ($endRaw instanceof \Carbon\CarbonInterface)
                            ? \Carbon\Carbon::instance($endRaw)
                            : \Carbon\Carbon::createFromFormat('Y-m-d', substr((string) $endRaw, 0, 10));

                        $startRaw  = $get('start_date');
                        $startYear = ($startRaw instanceof \Carbon\CarbonInterface)
                            ? $startRaw->year
                            : (is_string($startRaw) ? (int) substr($startRaw, 0, 4) : now()->year);

                        $late = [];
                        foreach (preg_split('/[\s,;]+/', trim($notes)) as $token) {
                            $token = trim($token);
                            if (! preg_match('#^(\d{1,2})/(\d{1,2})(?:/(\d{4}))?$#', $token, $m)) continue;
                            $day   = (int) $m[1];
                            $month = (int) $m[2];
                            $y     = isset($m[3]) ? (int) $m[3] : $startYear;
                            try {
                                $date = \Carbon\Carbon::createFromDate($y, $month, $day);
                                if ($date->gt($endCarbon)) $late[] = $date->format('d/m');
                            } catch (\Throwable) {}
                        }
                        return empty($late)
                            ? null
                            : 'Sessions fora del període: ' . implode(', ', $late) . ' (data final: ' . $endCarbon->format('d/m/Y') . ').';
                    })
                    ->hintColor('warning')
                    ->hintAction(
                        Action::make('generateSessionDates')
                            ->label('Auto-generar')
                            ->icon('heroicon-m-calendar-days')
                            ->action(function (Get $get, Set $set): void {
                                $startDate = $get('start_date');
                                $sessions  = (int) $get('sessions');
                                if (! $startDate || $sessions < 1) return;
                                $date  = \Carbon\Carbon::parse($startDate);
                                $dates = [];
                                for ($i = 0; $i < $sessions; $i++) {
                                    $dates[] = $date->day . '/' . $date->month;
                                    $date->addWeek();
                                }
                                $set('calendar_notes', implode(', ', $dates));
                            })
                    ),

                TextInput::make('sessions')
                    ->label(__('site.course_sessions'))
                    ->numeric()->minValue(1)
                    ->disabled($lockAll),

                TextInput::make('hours')
                    ->label(__('site.course_hours'))
                    ->numeric()->minValue(1)
                    ->disabled($lockAll),
            ]),

            // 3. Espai i Horari
            Section::make(__('site.course_space_slot'))->columns(2)->schema([
                Select::make('space_id')
                    ->label(__('site.space'))
                    ->relationship('space', 'name')
                    ->searchable()->preload()->native(false)->nullable()
                    ->required(fn(Get $get): bool =>
                        $get('status') === 'active'
                        && in_array($get('format'), ['presencial', 'semipresencial', 'hibrid'])
                    )
                    ->validationMessages(['required' => 'Cal assignar un espai per activar un curs presencial.'])
                    ->disabled($lockAll),

                Select::make('time_slot_id')
                    ->label(__('site.timeslot'))
                    ->relationship('timeSlot', 'description')
                    ->searchable()->preload()->native(false)->nullable()
                    ->required(fn(Get $get): bool =>
                        $get('status') === 'active'
                        && in_array($get('format'), ['presencial', 'semipresencial', 'hibrid'])
                    )
                    ->validationMessages(['required' => 'Cal assignar una franja horària per activar un curs presencial.'])
                    ->disabled($lockAll),
            ]),

            // 4. Format i Places
            Section::make(__('site.course_format_pl'))->columns(2)->schema([
                Select::make('format')
                    ->label(__('site.course_format'))
                    ->options(__('site.formats'))
                    ->required()->native(false)->default('presencial')
                    ->disabled($lockIdentity),

                TextInput::make('max_students')
                    ->label(__('site.course_max'))
                    ->helperText(__('site.course_max_hint'))
                    ->numeric()->minValue(1)->nullable()
                    ->disabled($lockMaxStudents),

                TextInput::make('price')
                    ->label(__('site.course_price'))
                    ->numeric()->prefix('€')->default(0)->minValue(0)
                    ->disabled($lockIdentity),
            ]),

            // 5. Professors
            Section::make(__('site.course_teachers'))->schema([
                Select::make('teachers')
                    ->label(__('site.teachers'))
                    ->multiple()
                    ->relationship('teachers', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                    ->searchable()->preload()
                    ->disabled($lockAll),
            ]),

            // 6. Contingut
            Section::make(__('site.course_content'))->schema([
                Textarea::make('description')
                    ->label(__('site.description'))
                    ->rows(3)->columnSpanFull()
                    ->disabled($lockAll),

                Textarea::make('objectives')
                    ->label(__('site.course_objectives'))
                    ->rows(3)->columnSpanFull()
                    ->disabled($lockAll),

                Textarea::make('requirements')
                    ->label(__('site.course_requirements'))
                    ->rows(2)->columnSpanFull()
                    ->disabled($lockAll),
            ]),

            // 7. Configuració
            Section::make(__('site.course_config'))->columns(2)->schema([
                Select::make('status')
                    ->label(__('site.status'))
                    ->options(__('site.course_statuses'))
                    ->required()->native(false)->default('draft')
                    ->live()
                    ->hintIcon('heroicon-m-information-circle')
                    ->hintIconTooltip(
                        "Esborrany: sense restriccions\n" .
                        "Planificació: cal codi, temporada, format i dates\n" .
                        "Actiu: +espai, horari, preu i ≥1 professor (presencial)\n" .
                        "Tancat: sols admin pot editar"
                    )
                    ->disabled($lockAll),

                Toggle::make('is_public')
                    ->label(__('site.course_is_public'))
                    ->helperText(__('site.course_is_public_hint'))
                    ->default(true)->inline(false)
                    ->disabled($lockAll),

                Toggle::make('open_enrollment')
                    ->label('Inscripció sempre oberta')
                    ->helperText('Permet inscripcions en qualsevol moment, sense restricció de temporada ni de dates. Ideal per a cursos online o LMS.')
                    ->default(false)->inline(false)->columnSpanFull()
                    ->disabled($lockAll),
            ]),
        ]);
    }

    // ── Taula ──────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Sempre visible — identificació mínima
                Tables\Columns\TextColumn::make('title')
                    ->label(__('site.course_title'))
                    ->searchable()->sortable()->weight('bold')
                    ->description(fn(CampusCourse $r) => $r->parent
                        ? '↳ ' . $r->parent->title
                        : ($r->children()->count() > 0
                            ? '📋 ' . __('site.template')
                            : null)),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('site.status'))
                    ->formatStateUsing(fn($state) => __('site.course_statuses')[$state] ?? $state)
                    ->badge()
                    ->color(fn($state) => CampusCourse::STATUS_COLORS[$state] ?? 'gray'),

                // Activables ON per defecte
                Tables\Columns\TextColumn::make('code')
                    ->label(__('site.code'))
                    ->badge()->color('gray')
                    ->searchable()->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('season.name')
                    ->label(__('site.season'))
                    ->badge()->color('info')->sortable()
                    ->toggleable(),

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
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('site.course_price'))
                    ->money('EUR')->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('site.public'))->boolean()
                    ->trueColor('success')->falseColor('gray')
                    ->toggleable(),

                // Activables OFF per defecte
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('site.category'))
                    ->badge()
                    ->color(fn($record) => $record->category?->color ?? 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('max_students')
                    ->label(__('site.course_max'))
                    ->formatStateUsing(fn($state) => $state ?? '∞')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('open_enrollment')
                    ->label('∞ Inscr.')
                    ->boolean()
                    ->trueColor('info')->falseColor('gray')
                    ->trueIcon('heroicon-o-lock-open')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label(__('site.public'))
                    ->trueLabel(__('site.only_public'))
                    ->falseLabel(__('site.only_private'))
                    ->native(false),

                Tables\Filters\TernaryFilter::make('course_type')
                    ->label('Tipus')
                    ->placeholder('Tots')
                    ->trueLabel('Sols plantilles / pare')
                    ->falseLabel('Sols edicions')
                    ->queries(
                        true:  fn($query) => $query->whereNull('parent_id'),
                        false: fn($query) => $query->whereNotNull('parent_id'),
                    )
                    ->native(false),
            ])
            ->actions([
                EditAction::make()->label(__('site.edit')),
                ActionGroup::make([
                    Action::make('clone')
                        ->label('Clonar')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading(fn(CampusCourse $r) => 'Clonar "' . $r->title . '"')
                        ->modalDescription('Es crearà una còpia en estat Esborrany. Podràs ajustar temporada, dates i codi a continuació.')
                        ->modalSubmitActionLabel('Clonar')
                        ->action(function (CampusCourse $record) {
                            $clone = $record->replicate();
                            $clone->status         = 'draft';
                            $clone->start_date     = null;
                            $clone->end_date       = null;
                            $clone->calendar_notes = null;
                            $clone->code           = null;
                            $clone->slug           = null;
                            $clone->parent_id      = $record->parent_id ?? $record->id;
                            $clone->save();

                            return redirect(CourseResource::getUrl('edit', ['record' => $clone]));
                        }),

                    Action::make('preview')
                        ->label('Previsualitzar')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->url(fn(CampusCourse $r) => route('campus.catalog.show', ['slug' => $r->slug, 'preview' => 1]))
                        ->openUrlInNewTab(),

                    DeleteAction::make()->label(__('site.delete')),
                ])->iconButton()->tooltip('Més accions'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('site.delete_selected')),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) CampusCourse::where('status', 'active')->count();
    }

    public static function getRelationManagers(): array
    {
        return [
            \App\Filament\Resources\CourseResource\RelationManagers\DocumentsRelationManager::class,
            \App\Filament\Resources\CourseResource\RelationManagers\LessonsRelationManager::class,
            \App\Filament\Resources\CourseResource\RelationManagers\LessonResponsesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'    => Pages\ListCourses::route('/'),
            'create'   => Pages\CreateCourse::route('/create'),
            'edit'     => Pages\EditCourse::route('/{record}/edit'),
            'students' => Pages\ViewCourseStudents::route('/{record}/students'),
        ];
    }
}
