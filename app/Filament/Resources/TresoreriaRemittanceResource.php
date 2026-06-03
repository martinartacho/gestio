<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TresoreriaRemittanceResource\Pages;
use App\Models\AssociatSepaRemittance;
use App\Models\TresoreriaRemittance;
use App\Settings\SettingStore;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TresoreriaRemittanceResource extends Resource
{
    protected static ?string $model = TresoreriaRemittance::class;
    protected static ?string $slug  = 'tresoreria-remittances';
    protected static ?int    $navigationSort = 6;

    public static function getNavigationIcon(): string   { return 'heroicon-o-document-arrow-down'; }
    public static function getNavigationGroup(): string  { return __('site.treasury_group'); }
    public static function getNavigationLabel(): string  { return 'Remeses SEPA socis'; }
    public static function getModelLabel(): string       { return 'remesa SEPA'; }
    public static function getPluralModelLabel(): string { return 'remeses SEPA socis'; }

    public static function canAccess(): bool
    {
        $store = app(SettingStore::class);
        return (bool) $store->get('tresoreria_enabled', true)
            && (bool) $store->get('tresoreria_sepa_socis_enabled', true)
            && (auth()->user()?->hasPermissionTo('payments.view') ?? false);
    }

    public static function canCreate(): bool                                       { return auth()->user()?->hasPermissionTo('payments.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool   { return auth()->user()?->hasPermissionTo('payments.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->hasPermissionTo('payments.delete') ?? false; }
    public static function canDeleteAny(): bool                                    { return auth()->user()?->hasPermissionTo('payments.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Remesa SEPA')->schema([
                TextInput::make('reference')
                    ->label('Referència')
                    ->placeholder('Es genera automàticament si es deixa buit')
                    ->unique(ignoreRecord: true)
                    ->maxLength(35),

                TextInput::make('year')
                    ->label('Any de la quota')
                    ->numeric()
                    ->default(now()->year)
                    ->required(),

                DatePicker::make('execution_date')
                    ->label('Data d\'execució del càrrec')
                    ->displayFormat('d/m/Y')
                    ->required(),

                Select::make('status')
                    ->label('Estat')
                    ->options([
                        'draft'     => 'Esborrany',
                        'generated' => 'XML generat',
                        'submitted' => 'Enviat al banc',
                        'processed' => 'Processat',
                    ])
                    ->default('draft')
                    ->required(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Referència')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('year')
                    ->label('Any')
                    ->sortable(),

                Tables\Columns\TextColumn::make('execution_date')
                    ->label('Data execució')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_transactions')
                    ->label('Socis')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Import total')
                    ->money('EUR'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estat')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'draft'     => 'gray',
                        'generated' => 'info',
                        'submitted' => 'warning',
                        'processed' => 'success',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft'     => 'Esborrany',
                        'generated' => 'XML generat',
                        'submitted' => 'Enviat al banc',
                        'processed' => 'Processat',
                        default     => $state,
                    }),

                Tables\Columns\TextColumn::make('generated_at')
                    ->label('Generat')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTresoreriaRemittances::route('/'),
            'create' => Pages\CreateTresoreriaRemittance::route('/create'),
            'edit'   => Pages\EditTresoreriaRemittance::route('/{record}/edit'),
        ];
    }
}
