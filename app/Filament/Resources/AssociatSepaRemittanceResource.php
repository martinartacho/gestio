<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssociatSepaRemittanceResource\Pages;
use App\Models\AssociatSepaRemittance;
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

class AssociatSepaRemittanceResource extends Resource
{
    protected static ?string $model = AssociatSepaRemittance::class;
    protected static ?int    $navigationSort = 15;

    public static function getNavigationIcon(): string   { return 'heroicon-o-document-arrow-down'; }
    public static function getNavigationGroup(): string  { return __('Associats'); }
    public static function getNavigationLabel(): string  { return __('Remeses SEPA'); }
    public static function getModelLabel(): string       { return 'remesa'; }
    public static function getPluralModelLabel(): string { return 'remeses SEPA'; }

    public static function canAccess(): bool
    {
        $store = app(SettingStore::class);
        return (bool) $store->get('associats_enabled', false)
            && (bool) $store->get('associats_sepa_enabled', true)
            && (auth()->user()?->hasPermissionTo('sepa.view') ?? false);
    }

    public static function canCreate(): bool                                       { return auth()->user()?->hasPermissionTo('sepa.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool   { return auth()->user()?->hasPermissionTo('sepa.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->hasPermissionTo('sepa.delete') ?? false; }
    public static function canDeleteAny(): bool                                    { return auth()->user()?->hasPermissionTo('sepa.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Remesa SEPA'))->schema([
                TextInput::make('reference')
                    ->label(__('Referència'))
                    ->placeholder(__('Es genera automàticament si es deixa buit'))
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('tenant_id', current_tenant()?->id))
                    ->maxLength(35),

                TextInput::make('year')
                    ->label(__('Any de la quota'))
                    ->numeric()
                    ->default(now()->year)
                    ->required(),

                DatePicker::make('execution_date')
                    ->label(__('Data d\'execució del càrrec'))
                    ->displayFormat('d/m/Y')
                    ->required(),

                Select::make('status')
                    ->label(__('Estat'))
                    ->options([
                        'draft'     => __('Esborrany'),
                        'generated' => __('XML generat'),
                        'submitted' => __('Enviat al banc'),
                        'processed' => __('Processat'),
                    ])
                    ->default('draft')
                    ->required(),

                Textarea::make('notes')
                    ->label(__('Notes'))
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
                    ->label(__('Referència'))
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('year')
                    ->label(__('Any'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('execution_date')
                    ->label(__('Data execució'))
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_transactions')
                    ->label(__('Socis'))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label(__('Import total'))
                    ->money('EUR'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Estat'))
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'draft'     => 'gray',
                        'generated' => 'info',
                        'submitted' => 'warning',
                        'processed' => 'success',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft'     => __('Esborrany'),
                        'generated' => __('XML generat'),
                        'submitted' => __('Enviat al banc'),
                        'processed' => __('Processat'),
                        default     => $state,
                    }),

                Tables\Columns\TextColumn::make('generated_at')
                    ->label(__('Generat'))
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssociatSepaRemittances::route('/'),
            'create' => Pages\CreateAssociatSepaRemittance::route('/create'),
            'edit'   => Pages\EditAssociatSepaRemittance::route('/{record}/edit'),
        ];
    }
}
