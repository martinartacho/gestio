<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\CampusEnrollment;
use App\Models\CampusPayment;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{DatePicker, Select, Textarea, TextInput};
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = CampusPayment::class;
    protected static ?int    $navigationSort = 2;

    public static function getNavigationIcon(): string   { return 'heroicon-o-banknotes'; }
    public static function getNavigationLabel(): string  { return __('site.payments'); }
    public static function getNavigationGroup(): string  { return __('site.treasury_group'); }
    public static function getModelLabel(): string       { return __('site.payment'); }
    public static function getPluralModelLabel(): string { return __('site.payments'); }

    public static function canAccess(): bool                                          { return auth()->user()?->hasPermissionTo('payments.view')   ?? false; }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('payments.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('payments.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('payments.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('payments.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('site.payment_detail'))->columns(2)->schema([
                Select::make('enrollment_id')
                    ->label(__('site.enrollment'))
                    ->relationship('enrollment', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn(CampusEnrollment $record) =>
                        "{$record->first_name} {$record->last_name} — {$record->course?->title}"
                    )
                    ->searchable()->required()->native(false)->columnSpanFull(),

                TextInput::make('amount')
                    ->label(__('site.payment_amount'))
                    ->numeric()->prefix('€')->required()->minValue(0),

                Select::make('method')
                    ->label(__('site.payment_method'))
                    ->options(CampusPayment::METHODS)
                    ->default('transfer')->required()->native(false),

                DatePicker::make('payment_date')
                    ->label(__('site.payment_date'))
                    ->default(now())->required()->native(false),

                Select::make('status')
                    ->label(__('site.status'))
                    ->options(CampusPayment::STATUSES)
                    ->default('pending')->required()->native(false),

                TextInput::make('reference')
                    ->label(__('site.payment_reference'))
                    ->maxLength(100)->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('enrollment.full_name')
                    ->label(__('site.student'))
                    ->searchable(['campus_enrollments.first_name', 'campus_enrollments.last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrollment.course.title')
                    ->label(__('site.course'))
                    ->limit(30),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('site.payment_amount'))
                    ->money('EUR')->sortable(),

                Tables\Columns\TextColumn::make('method')
                    ->label(__('site.payment_method'))
                    ->formatStateUsing(fn($state) => CampusPayment::METHODS[$state] ?? $state)
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label(__('site.payment_date'))
                    ->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('site.status'))
                    ->formatStateUsing(fn($state) => CampusPayment::STATUSES[$state] ?? $state)
                    ->badge()
                    ->color(fn($state) => CampusPayment::STATUS_COLORS[$state] ?? 'gray'),

                Tables\Columns\TextColumn::make('reference')
                    ->label(__('site.payment_reference'))
                    ->searchable()->placeholder('—'),
            ])
            ->defaultSort('payment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options(CampusPayment::STATUSES)
                    ->native(false),

                Tables\Filters\SelectFilter::make('method')
                    ->label(__('site.payment_method'))
                    ->options(CampusPayment::METHODS)
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
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
