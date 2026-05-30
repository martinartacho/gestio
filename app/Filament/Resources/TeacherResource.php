<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\CampusTeacher;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{DatePicker, DateTimePicker, Select, TagsInput, Textarea, TextInput, Toggle};
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherResource extends Resource
{
    protected static ?string $model = CampusTeacher::class;
    protected static ?int    $navigationSort = 2;

    public static function getNavigationIcon(): string   { return 'heroicon-o-academic-cap'; }
    public static function getNavigationLabel(): string  { return __('site.teachers'); }
    public static function getNavigationGroup(): string  { return __('site.courses_group'); }
    public static function getModelLabel(): string       { return __('site.teacher'); }
    public static function getPluralModelLabel(): string { return __('site.teachers'); }

    public static function canAccess(): bool                                          { return app(\App\Settings\SettingStore::class)->get('campus_enabled', true) && (auth()->user()?->hasPermissionTo('teachers.view') ?? false); }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('teachers.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('teachers.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('teachers.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('teachers.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        $isTresoreria = fn() => auth()->user()?->hasAnyRole(['admin', 'tresoreria']);

        return $schema->components([

            // ── Dades personals ───────────────────────────────────────────
            Section::make(__('site.teacher_personal'))->columns(3)->schema([
                TextInput::make('code')
                    ->label(__('site.teacher_code'))
                    ->maxLength(20)->unique(ignoreRecord: true)
                    ->placeholder('ex: JM, ABC'),

                TextInput::make('degree')
                    ->label(__('site.teacher_degree'))
                    ->maxLength(20)->placeholder('Dr., Dra., Prof.'),

                TextInput::make('first_name')
                    ->label(__('site.teacher_firstname'))
                    ->required()->maxLength(100),

                TextInput::make('last_name')
                    ->label(__('site.teacher_lastname'))
                    ->required()->maxLength(100),

                TextInput::make('title')
                    ->label(__('site.teacher_title'))
                    ->maxLength(150),

                TextInput::make('specialization')
                    ->label(__('site.teacher_spec'))
                    ->maxLength(150),

                TagsInput::make('areas')
                    ->label(__('site.teacher_areas'))
                    ->columnSpanFull(),

                Textarea::make('bio')
                    ->label(__('site.teacher_bio'))
                    ->rows(3)->columnSpanFull(),
            ]),

            // ── Contacte ──────────────────────────────────────────────────
            Section::make(__('site.teacher_contact'))->columns(2)->schema([
                TextInput::make('email')
                    ->label(__('site.email'))
                    ->email()->maxLength(150),

                TextInput::make('phone')
                    ->label(__('site.phone'))
                    ->tel()->maxLength(20),

                Select::make('status')
                    ->label(__('site.teacher_status'))
                    ->options(CampusTeacher::STATUSES)
                    ->default('active')->required()->native(false),

                DatePicker::make('hiring_date')
                    ->label(__('site.teacher_hiring_date'))
                    ->nullable()->native(false),

                Textarea::make('observacions')
                    ->label(__('site.teacher_observacions'))
                    ->rows(2)->columnSpanFull(),
            ]),

            // ── Adreça i identificació ────────────────────────────────────
            Section::make(__('site.teacher_address_section'))->columns(2)
                ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'manager', 'tresoreria']))
                ->schema([
                    TextInput::make('dni')
                        ->label(__('site.teacher_dni'))
                        ->maxLength(20),

                    TextInput::make('address')
                        ->label(__('site.teacher_address'))
                        ->maxLength(255)->columnSpanFull(),

                    TextInput::make('city')
                        ->label(__('site.teacher_city'))
                        ->maxLength(100),

                    TextInput::make('postal_code')
                        ->label(__('site.teacher_postal_code'))
                        ->maxLength(10),
                ]),

            // ── Dades fiscals ─────────────────────────────────────────────
            Section::make(__('site.teacher_fiscal'))->columns(2)
                ->visible($isTresoreria)
                ->schema([
                    Select::make('fiscal_situation')
                        ->label(__('site.fiscal_situation'))
                        ->options(CampusTeacher::FISCAL_SITUATIONS)
                        ->nullable()->native(false),

                    Select::make('payment_type')
                        ->label(__('site.payment_type'))
                        ->options(CampusTeacher::PAYMENT_TYPES)
                        ->nullable()->native(false),

                    Toggle::make('needs_payment')
                        ->label(__('site.needs_payment'))
                        ->default(true)->inline(false),

                    Toggle::make('invoice')
                        ->label(__('site.teacher_invoice'))
                        ->nullable()->inline(false),
                ]),

            // ── Dades bancàries ───────────────────────────────────────────
            Section::make(__('site.teacher_bank'))->columns(2)
                ->visible($isTresoreria)
                ->schema([
                    TextInput::make('bank_iban')
                        ->label(__('site.bank_iban'))
                        ->maxLength(34)->placeholder('ES00 0000 0000 00 0000000000'),

                    TextInput::make('bank_holder')
                        ->label(__('site.bank_holder'))
                        ->maxLength(150),

                    TextInput::make('fiscal_id')
                        ->label(__('site.fiscal_id'))
                        ->maxLength(20),
                ]),

            // ── Beneficiari ───────────────────────────────────────────────
            Section::make(__('site.teacher_beneficiary'))->columns(2)
                ->visible($isTresoreria)
                ->schema([
                    TextInput::make('beneficiary_dni')
                        ->label(__('site.beneficiary_dni'))
                        ->maxLength(20),

                    TextInput::make('beneficiary_iban')
                        ->label(__('site.beneficiary_iban'))
                        ->maxLength(34),

                    TextInput::make('beneficiary_holder')
                        ->label(__('site.beneficiary_holder'))
                        ->maxLength(150),

                    Select::make('beneficiary_fiscal_situation')
                        ->label(__('site.beneficiary_fiscal_situation'))
                        ->options(CampusTeacher::FISCAL_SITUATIONS)
                        ->nullable()->native(false),

                    TextInput::make('beneficiary_city')
                        ->label(__('site.beneficiary_city'))
                        ->maxLength(100),

                    TextInput::make('beneficiary_postal_code')
                        ->label(__('site.beneficiary_postal_code'))
                        ->maxLength(10),

                    Toggle::make('beneficiary_invoice')
                        ->label(__('site.beneficiary_invoice'))
                        ->nullable()->inline(false),
                ]),

            // ── RGPD i consentiments ──────────────────────────────────────
            Section::make(__('site.teacher_rgpd'))->columns(3)
                ->visible($isTresoreria)
                ->schema([
                    Toggle::make('data_consent')
                        ->label(__('site.data_consent'))
                        ->default(false)->inline(false),

                    Toggle::make('fiscal_responsibility')
                        ->label(__('site.fiscal_responsibility'))
                        ->default(false)->inline(false),

                    Toggle::make('ceded_confirmation')
                        ->label(__('site.ceded_confirmation'))
                        ->default(false)->inline(false),
                ]),

            // ── Estat del pagament ────────────────────────────────────────
            Section::make(__('site.teacher_payment_tracking'))->columns(2)
                ->visible($isTresoreria)
                ->schema([
                    Select::make('payment_status')
                        ->label(__('site.teacher_payment_status'))
                        ->options(CampusTeacher::PAYMENT_STATUSES)
                        ->default('pending')->required()->native(false),

                    DateTimePicker::make('payment_confirmed_at')
                        ->label(__('site.payment_confirmed_at'))
                        ->nullable()->native(false),

                    TextInput::make('payment_pdf_path')
                        ->label(__('site.payment_pdf_path'))
                        ->maxLength(500)->columnSpanFull(),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('site.teacher_code'))
                    ->searchable()->sortable()
                    ->badge()->color('gray')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('site.teacher'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable('last_name')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('specialization')
                    ->label(__('site.teacher_spec'))
                    ->searchable()->limit(40),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('site.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('courses_count')
                    ->label(__('site.courses'))
                    ->counts('courses')->badge()->color('primary'),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label(__('site.teacher_payment_status'))
                    ->formatStateUsing(fn($state) => CampusTeacher::PAYMENT_STATUSES[$state] ?? $state)
                    ->badge()
                    ->color(fn($state) => CampusTeacher::PAYMENT_STATUS_COLORS[$state] ?? 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('site.status'))
                    ->formatStateUsing(fn($state) => CampusTeacher::STATUSES[$state] ?? $state)
                    ->badge()
                    ->color(fn($state) => $state === 'active' ? 'success' : 'gray'),
            ])
            ->defaultSort('last_name')
            ->recordAction(null)
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options(CampusTeacher::STATUSES)
                    ->native(false),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label(__('site.teacher_payment_status'))
                    ->options(CampusTeacher::PAYMENT_STATUSES)
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
        return (string) CampusTeacher::where('status', 'active')->count();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit'   => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
