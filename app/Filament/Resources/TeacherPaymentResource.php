<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherPaymentResource\Pages;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use App\Models\CampusTeacher;
use App\Models\CampusTeacherPayment;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{DateTimePicker, Select, Textarea, TextInput, Toggle};
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherPaymentResource extends Resource
{
    protected static ?string $model = CampusTeacherPayment::class;
    protected static ?int    $navigationSort = 3;

    public static function getNavigationIcon(): string   { return 'heroicon-o-document-text'; }
    public static function getNavigationLabel(): string  { return __('site.teacher_payments'); }
    public static function getNavigationGroup(): string  { return __('site.treasury_group'); }
    public static function getModelLabel(): string       { return __('site.teacher_payment'); }
    public static function getPluralModelLabel(): string { return __('site.teacher_payments'); }

    public static function canAccess(): bool                                          { return auth()->user()?->hasPermissionTo('teacher_payments.view')   ?? false; }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('teacher_payments.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('teacher_payments.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('teacher_payments.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('teacher_payments.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('site.teacher_payment_detail'))->columns(2)->schema([
                Select::make('season_id')
                    ->label(__('site.season'))
                    ->options(fn() => CampusSeason::orderByDesc('year')->orderBy('quadrimester')
                        ->get()->mapWithKeys(fn($s) => [$s->id => "{$s->year} — {$s->name}"]))
                    ->required()->native(false)->searchable()->columnSpanFull(),

                Select::make('teacher_id')
                    ->label(__('site.teacher'))
                    ->options(fn() => CampusTeacher::orderBy('last_name')->orderBy('first_name')
                        ->get()->mapWithKeys(fn($t) => [$t->id => "{$t->last_name}, {$t->first_name}"]))
                    ->required()->native(false)->searchable(),

                Select::make('course_id')
                    ->label(__('site.course'))
                    ->options(fn() => CampusCourse::where('status', '!=', 'draft')
                        ->orderBy('title')->get()->mapWithKeys(fn($c) => [$c->id => $c->title]))
                    ->nullable()->native(false)->searchable(),
            ]),

            Section::make(__('site.teacher_payment_amounts'))->columns(3)->schema([
                TextInput::make('sessions_description')
                    ->label(__('site.sessions_description'))
                    ->maxLength(100)->columnSpanFull(),

                TextInput::make('sessions_count')
                    ->label(__('site.sessions_count'))
                    ->numeric()->integer()->minValue(0)->default(0)
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $gross = ($state ?? 0) * ($get('price_per_session') ?? 0);
                        $set('gross_amount', round($gross, 2));
                        $ret = $gross * ($get('retention_pct') ?? 0) / 100;
                        $set('retention_amount', round($ret, 2));
                        $set('net_amount', round($gross - $ret, 2));
                    }),

                TextInput::make('price_per_session')
                    ->label(__('site.price_per_session'))
                    ->numeric()->prefix('€')->minValue(0)->default(0)
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $gross = ($get('sessions_count') ?? 0) * ($state ?? 0);
                        $set('gross_amount', round($gross, 2));
                        $ret = $gross * ($get('retention_pct') ?? 0) / 100;
                        $set('retention_amount', round($ret, 2));
                        $set('net_amount', round($gross - $ret, 2));
                    }),

                TextInput::make('gross_amount')
                    ->label(__('site.gross_amount'))
                    ->numeric()->prefix('€')->minValue(0)->default(0),

                TextInput::make('retention_pct')
                    ->label(__('site.retention_pct'))
                    ->numeric()->suffix('%')->minValue(0)->maxValue(100)->default(0)
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $gross = $get('gross_amount') ?? 0;
                        $ret = $gross * ($state ?? 0) / 100;
                        $set('retention_amount', round($ret, 2));
                        $set('net_amount', round($gross - $ret, 2));
                    }),

                TextInput::make('retention_amount')
                    ->label(__('site.retention_amount'))
                    ->numeric()->prefix('€')->minValue(0)->default(0),

                TextInput::make('net_amount')
                    ->label(__('site.net_amount'))
                    ->numeric()->prefix('€')->minValue(0)->default(0),

                Toggle::make('issues_invoice')
                    ->label(__('site.issues_invoice'))
                    ->inline(false)->columnSpanFull(),
            ]),

            Section::make(__('site.status'))->columns(3)->schema([
                Select::make('status')
                    ->label(__('site.status'))
                    ->options(CampusTeacherPayment::STATUSES)
                    ->default('draft')->required()->native(false),

                DateTimePicker::make('sent_at')
                    ->label(__('site.sent_at'))
                    ->nullable()->native(false),

                DateTimePicker::make('paid_at')
                    ->label(__('site.paid_at'))
                    ->nullable()->native(false),
            ]),

            Section::make(__('site.notes'))->schema([
                Textarea::make('notes')->label('')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.full_name')
                    ->label(__('site.teacher'))
                    ->searchable(['campus_teachers.first_name', 'campus_teachers.last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('season.name')
                    ->label(__('site.season'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.title')
                    ->label(__('site.course'))
                    ->limit(30)->placeholder('—'),

                Tables\Columns\TextColumn::make('sessions_count')
                    ->label(__('site.sessions'))
                    ->numeric()->alignCenter(),

                Tables\Columns\TextColumn::make('gross_amount')
                    ->label(__('site.gross_amount'))
                    ->money('EUR')->sortable(),

                Tables\Columns\TextColumn::make('net_amount')
                    ->label(__('site.net_amount'))
                    ->money('EUR')->sortable()->weight('bold'),

                Tables\Columns\IconColumn::make('issues_invoice')
                    ->label(__('site.invoice'))
                    ->boolean()->alignCenter(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('site.status'))
                    ->formatStateUsing(fn($state) => CampusTeacherPayment::STATUSES[$state] ?? $state)
                    ->badge()
                    ->color(fn($state) => CampusTeacherPayment::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options(CampusTeacherPayment::STATUSES)
                    ->native(false),

                Tables\Filters\SelectFilter::make('season_id')
                    ->label(__('site.season'))
                    ->options(fn() => CampusSeason::orderByDesc('year')->get()
                        ->mapWithKeys(fn($s) => [$s->id => $s->name]))
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeacherPayments::route('/'),
            'create' => Pages\CreateTeacherPayment::route('/create'),
            'edit'   => Pages\EditTeacherPayment::route('/{record}/edit'),
        ];
    }
}
