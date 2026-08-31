<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssociatMemberResource\Pages;
use App\Models\AssociatMember;
use App\Settings\SettingStore;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AssociatMemberResource extends Resource
{
    protected static ?string $model = AssociatMember::class;
    protected static ?int    $navigationSort = 5;

    public static function getNavigationIcon(): string   { return 'heroicon-o-identification'; }
    public static function getNavigationGroup(): string  { return 'Associats'; }
    public static function getNavigationLabel(): string  { return 'Socis'; }
    public static function getModelLabel(): string       { return 'soci'; }
    public static function getPluralModelLabel(): string { return 'socis'; }

    public static function canAccess(): bool
    {
        $store = app(SettingStore::class);
        return (bool) $store->get('associats_enabled', false)
            && (bool) $store->get('associats_socis_enabled', true)
            && (auth()->user()?->hasPermissionTo('members.view') ?? false);
    }

    public static function canCreate(): bool                                       { return auth()->user()?->hasPermissionTo('members.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool   { return auth()->user()?->hasPermissionTo('members.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->hasPermissionTo('members.delete') ?? false; }
    public static function canDeleteAny(): bool                                    { return auth()->user()?->hasPermissionTo('members.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Identificació')->schema([
                TextInput::make('member_number')
                    ->label('Número de soci')
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('tenant_id', current_tenant()?->id))
                    ->maxLength(20),

                Select::make('status')
                    ->label('Estat')
                    ->options([
                        'active'    => 'Actiu',
                        'pending'   => 'Pendent',
                        'cancelled' => 'Baixa',
                    ])
                    ->required()
                    ->default('pending'),

                TextInput::make('first_name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(100),

                TextInput::make('last_name')
                    ->label('Cognoms')
                    ->required()
                    ->maxLength(100),
            ])->columns(2),

            Section::make('Contacte')->schema([
                TextInput::make('email')
                    ->label('Correu electrònic')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('tenant_id', current_tenant()?->id)),

                TextInput::make('phone')
                    ->label('Telèfon')
                    ->tel()
                    ->maxLength(20),

                TextInput::make('address')
                    ->label('Adreça')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('postal_code')
                    ->label('Codi postal')
                    ->maxLength(10),

                TextInput::make('city')
                    ->label('Municipi')
                    ->maxLength(100),
            ])->columns(2),

            Section::make('Dates d\'alta i baixa')->schema([
                DatePicker::make('joined_at')
                    ->label('Data d\'alta')
                    ->displayFormat('d/m/Y'),

                DatePicker::make('cancelled_at')
                    ->label('Data de baixa')
                    ->displayFormat('d/m/Y'),
            ])->columns(2),

            Section::make('Dades bancàries — Domiciliació SEPA')
                ->description('Informació necessària per generar fitxers de remesa SEPA (pain.008). Les dades bancàries es guarden xifrades.')
                ->schema([
                    TextInput::make('bank_iban')
                        ->label('IBAN')
                        ->placeholder('ES91 2100 0418 4502 0005 1332')
                        ->maxLength(34)
                        ->columnSpanFull(),

                    TextInput::make('bank_holder')
                        ->label('Titular del compte')
                        ->maxLength(100),

                    TextInput::make('mandate_reference')
                        ->label('Referència del mandat')
                        ->placeholder('SOCI-000123')
                        ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('tenant_id', current_tenant()?->id))
                        ->maxLength(35),

                    DatePicker::make('mandate_signed_at')
                        ->label('Data de signatura del mandat')
                        ->displayFormat('d/m/Y'),

                    Select::make('mandate_sequence')
                        ->label('Tipus de seqüència')
                        ->options([
                            'FRST' => 'FRST — Primera presentació',
                            'RCUR' => 'RCUR — Recurrent',
                        ])
                        ->helperText('FRST per al primer càrrec; RCUR per a successius.'),

                    Select::make('mandate_method')
                        ->label('Suport del mandat')
                        ->options([
                            'paper'       => 'Paper signat (escanejat)',
                            'electronic'  => 'Signatura electrònica (URL)',
                            'web'         => 'Formulari web (acceptació digital)',
                        ])
                        ->live()
                        ->columnSpanFull(),

                    FileUpload::make('mandate_document')
                        ->label('Document escanejat (PDF/imatge)')
                        ->disk('local')
                        ->directory('mandats-sepa')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(5120)
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull()
                        ->visible(fn ($get) => $get('mandate_method') === 'paper'),

                    TextInput::make('mandate_document')
                        ->label('URL del document signat electrònicament')
                        ->url()
                        ->placeholder('https://docusign.net/...')
                        ->columnSpanFull()
                        ->visible(fn ($get) => $get('mandate_method') === 'electronic'),

                    TextInput::make('mandate_ip')
                        ->label('IP d\'acceptació (web)')
                        ->placeholder('Omple automàticament si el soci accepta online')
                        ->maxLength(45)
                        ->columnSpanFull()
                        ->visible(fn ($get) => $get('mandate_method') === 'web'),

                ])->columns(2),

            Section::make('Accés al portal')->schema([
                TextInput::make('password')
                    ->label('Contrasenya')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context) => $context === 'create')
                    ->helperText('Deixa buit per no canviar la contrasenya actual.'),

                Toggle::make('data_consent')
                    ->label('Consentiment de dades (RGPD)')
                    ->default(false),
            ])->columns(2),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_number')
                    ->label('Nº soci')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->sortable(query: fn ($query, $direction) =>
                        $query->orderBy('first_name', $direction)->orderBy('last_name', $direction)
                    )
                    ->searchable(query: fn ($query, $search) =>
                        $query->where(fn ($q) =>
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                        )
                    ),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correu')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('Municipi')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estat')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger'  => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'active'    => 'Actiu',
                        'pending'   => 'Pendent',
                        'cancelled' => 'Baixa',
                        default     => $state,
                    }),

                Tables\Columns\IconColumn::make('bank_iban')
                    ->label('SEPA')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->bank_iban ? 'Dades SEPA registrades' : 'Sense dades SEPA'),

                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Alta')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estat')
                    ->options([
                        'active'    => 'Actiu',
                        'pending'   => 'Pendent',
                        'cancelled' => 'Baixa',
                    ]),

                Tables\Filters\TernaryFilter::make('bank_iban')
                    ->label('Dades SEPA')
                    ->nullable()
                    ->trueLabel('Amb SEPA')
                    ->falseLabel('Sense SEPA'),
            ])
            ->defaultSort('member_number')
            ->recordUrl(fn ($record) => static::getUrl('edit', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssociatMembers::route('/'),
            'create' => Pages\CreateAssociatMember::route('/create'),
            'edit'   => Pages\EditAssociatMember::route('/{record}/edit'),
        ];
    }
}
