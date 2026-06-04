<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssociatQuoteResource\Pages;
use App\Models\AssociatMember;
use App\Models\AssociatQuote;
use App\Settings\SettingStore;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AssociatQuoteResource extends Resource
{
    protected static ?string $model = AssociatQuote::class;
    protected static ?int    $navigationSort = 10;

    public static function getNavigationIcon(): string   { return 'heroicon-o-currency-euro'; }
    public static function getNavigationGroup(): string  { return 'Associats'; }
    public static function getNavigationLabel(): string  { return 'Quotes'; }
    public static function getModelLabel(): string       { return 'quota'; }
    public static function getPluralModelLabel(): string { return 'quotes'; }

    public static function canAccess(): bool
    {
        $store = app(SettingStore::class);
        return (bool) $store->get('associats_enabled', false)
            && (bool) $store->get('associats_quotes_enabled', true)
            && (auth()->user()?->hasPermissionTo('quotes.view') ?? false);
    }

    public static function canCreate(): bool                                       { return auth()->user()?->hasPermissionTo('quotes.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool   { return auth()->user()?->hasPermissionTo('quotes.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->hasPermissionTo('quotes.delete') ?? false; }
    public static function canDeleteAny(): bool                                    { return auth()->user()?->hasPermissionTo('quotes.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quota')->schema([
                Select::make('member_id')
                    ->label('Soci')
                    ->options(
                        AssociatMember::where('status', 'active')
                            ->orderBy('member_number')
                            ->get()
                            ->mapWithKeys(fn ($m) => [$m->id => "#{$m->member_number} — {$m->full_name}"])
                    )
                    ->searchable()
                    ->required(),

                TextInput::make('year')
                    ->label('Any')
                    ->numeric()
                    ->default(now()->year)
                    ->required(),

                Select::make('period')
                    ->label('Periodicitat')
                    ->options([
                        'annual'      => 'Anual',
                        'semi-annual' => 'Semestral',
                        'quarterly'   => 'Trimestral',
                        'monthly'     => 'Mensual',
                    ])
                    ->default('annual')
                    ->required(),

                TextInput::make('period_number')
                    ->label('Nº de període')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(12)
                    ->required(),

                TextInput::make('amount')
                    ->label('Import (€)')
                    ->numeric()
                    ->default(fn () => setting('associat_quota_amount', 0))
                    ->required(),

                Select::make('status')
                    ->label('Estat')
                    ->options([
                        'pending'   => 'Pendent',
                        'paid'      => 'Pagada',
                        'failed'    => 'Fallida',
                        'cancelled' => 'Cancel·lada',
                    ])
                    ->default('pending')
                    ->required(),

                DatePicker::make('due_date')
                    ->label('Data de venciment')
                    ->displayFormat('d/m/Y'),

                DatePicker::make('paid_at')
                    ->label('Data de pagament')
                    ->displayFormat('d/m/Y'),

                TextInput::make('failure_reason')
                    ->label('Motiu de fallida')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.member_number')
                    ->label('Nº soci')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('member.full_name')
                    ->label('Soci')
                    ->searchable(query: fn ($query, $search) =>
                        $query->whereHas('member', fn ($q) =>
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                        )
                    ),

                Tables\Columns\TextColumn::make('year')
                    ->label('Any')
                    ->sortable(),

                Tables\Columns\TextColumn::make('period')
                    ->label('Periodicitat')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'annual'      => 'indigo',
                        'semi-annual' => 'blue',
                        'quarterly'   => 'cyan',
                        'monthly'     => 'teal',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn ($state, $record) => match ($state) {
                        'annual'      => 'Anual',
                        'semi-annual' => "Semestral {$record->period_number}/2",
                        'quarterly'   => "Trimestral {$record->period_number}/4",
                        'monthly'     => "Mensual {$record->period_number}/12",
                        default       => $state,
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Import')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estat')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pending'   => 'warning',
                        'paid'      => 'success',
                        'failed'    => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending'   => 'Pendent',
                        'paid'      => 'Pagada',
                        'failed'    => 'Fallida',
                        'cancelled' => 'Cancel·lada',
                        default     => $state,
                    }),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Venciment')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remittance.reference')
                    ->label('Remesa')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estat')
                    ->options([
                        'pending'   => 'Pendent',
                        'paid'      => 'Pagada',
                        'failed'    => 'Fallida',
                        'cancelled' => 'Cancel·lada',
                    ]),

                Tables\Filters\SelectFilter::make('year')
                    ->label('Any')
                    ->options(fn () =>
                        AssociatQuote::distinct()->pluck('year', 'year')
                            ->sortDesc()->toArray()
                    ),

                Tables\Filters\SelectFilter::make('period')
                    ->label('Periodicitat')
                    ->options([
                        'annual'      => 'Anual',
                        'semi-annual' => 'Semestral',
                        'quarterly'   => 'Trimestral',
                        'monthly'     => 'Mensual',
                    ]),
            ])
            ->defaultSort('year', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssociatQuotes::route('/'),
            'create' => Pages\CreateAssociatQuote::route('/create'),
            'edit'   => Pages\EditAssociatQuote::route('/{record}/edit'),
        ];
    }
}
