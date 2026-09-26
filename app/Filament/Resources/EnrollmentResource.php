<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Mail\Campus\RefundConfirmationMail;
use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use Filament\Actions\{Action, BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{DatePicker, Select, Textarea, TextInput, Toggle};
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use Stripe\Refund as StripeRefund;
use Stripe\Stripe;

class EnrollmentResource extends Resource
{
    protected static ?string $model = CampusEnrollment::class;
    protected static ?int    $navigationSort = 1;

    public static function getNavigationIcon(): string   { return 'heroicon-o-user-plus'; }
    public static function getNavigationLabel(): string  { return __('site.enrollments'); }
    public static function getNavigationGroup(): string  { return __('site.treasury_group'); }
    public static function getModelLabel(): string       { return __('site.enrollment'); }
    public static function getPluralModelLabel(): string { return __('site.enrollments'); }

    public static function canAccess(): bool
    {
        $s = app(\App\Settings\SettingStore::class);
        return $s->get('campus_enabled', true)
            && $s->get('tresoreria_enabled', true)
            && $s->get('tresoreria_inscripcions_enabled', true)
            && (auth()->user()?->hasPermissionTo('enrollments.view') ?? false);
    }
    public static function canCreate(): bool                                          { return auth()->user()?->hasPermissionTo('enrollments.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool      { return auth()->user()?->hasPermissionTo('enrollments.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool    { return auth()->user()?->hasPermissionTo('enrollments.delete') ?? false; }
    public static function canDeleteAny(): bool                                       { return auth()->user()?->hasPermissionTo('enrollments.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('site.enrollment_course'))->schema([
                Select::make('course_id')
                    ->label(__('site.course'))
                    ->options(fn() => CampusCourse::where('status', '!=', 'draft')
                        ->with('season')
                        ->orderBy('title')
                        ->get()
                        ->mapWithKeys(fn($c) => [$c->id => "[{$c->season?->name}] {$c->title}"])
                    )
                    ->searchable()->required()->native(false),

                Select::make('status')
                    ->label(__('site.status'))
                    ->options(translated_labels(CampusEnrollment::STATUSES))
                    ->default('pending')->required()->native(false),

                DatePicker::make('enrollment_date')
                    ->label(__('site.enrollment_date'))
                    ->default(now())->required()->native(false),
            ])->columns(3),

            Section::make(__('site.enrollment_student'))->columns(2)->schema([
                TextInput::make('first_name')
                    ->label(__('site.first_name'))
                    ->required()->maxLength(100),

                TextInput::make('last_name')
                    ->label(__('site.last_name'))
                    ->required()->maxLength(100),

                TextInput::make('email')
                    ->label(__('site.email'))
                    ->email()->required()->maxLength(150),

                TextInput::make('phone')
                    ->label(__('site.phone'))
                    ->tel()->maxLength(20),

                TextInput::make('dni')
                    ->label(__('site.enrollment_dni'))
                    ->maxLength(20)->placeholder(__('NIF / NIE / Passaport')),
            ]),

            Section::make(__('site.enrollment_bank'))->columns(2)->schema([
                TextInput::make('bank_iban')
                    ->label('IBAN')
                    ->maxLength(34)
                    ->placeholder('ES00 0000 0000 0000 0000 0000'),

                TextInput::make('bank_holder')
                    ->label(__('site.enrollment_bank_holder'))
                    ->maxLength(150),
            ]),

            Section::make('RGPD')->schema([
                Toggle::make('rgpd_accepted')
                    ->label(__('site.enrollment_rgpd'))
                    ->live()
                    ->afterStateUpdated(fn($state, $set) =>
                        $set('rgpd_accepted_at', $state ? now()->toDateTimeString() : null)
                    ),

                DatePicker::make('rgpd_accepted_at')
                    ->label(__('site.enrollment_rgpd_at'))
                    ->native(false)
                    ->visible(fn($get) => $get('rgpd_accepted')),
            ])->columns(2),

            Section::make(__('site.notes'))->schema([
                Textarea::make('notes')->label('')->rows(3)->columnSpanFull(),
            ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('site.student'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable('last_name')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('site.email'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('course.title')
                    ->label(__('site.course'))
                    ->searchable()->limit(35),

                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label(__('site.enrollment_date'))
                    ->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('site.status'))
                    ->formatStateUsing(fn($state) => __(CampusEnrollment::STATUSES[$state] ?? $state))
                    ->badge()
                    ->color(fn($state) => CampusEnrollment::STATUS_COLORS[$state] ?? 'gray'),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Import'))
                    ->money('EUR', locale: 'ca')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('refunded_amount')
                    ->label(__('Retornat'))
                    ->money('EUR', locale: 'ca')
                    ->placeholder('—')
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('payment_reference')
                    ->label(__('Ref.'))
                    ->fontFamily('mono')
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('Mètode'))
                    ->formatStateUsing(fn($state) => __(CampusEnrollment::PAYMENT_METHODS[$state] ?? '—'))
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'stripe'   => 'blue',
                        'transfer' => 'warning',
                        'bizum'    => 'success',
                        'cash'     => 'gray',
                        'paypal'   => 'info',
                        default    => 'gray',
                    }),

                Tables\Columns\IconColumn::make('rgpd_accepted')
                    ->label('RGPD')
                    ->boolean(),

                Tables\Columns\TextColumn::make('payments_count')
                    ->label(__('site.payments'))
                    ->counts('payments')->badge()->color('primary'),
            ])
            ->defaultSort('enrollment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options(translated_labels(CampusEnrollment::STATUSES))
                    ->native(false),

                Tables\Filters\SelectFilter::make('course_id')
                    ->label(__('site.course'))
                    ->relationship('course', 'title')
                    ->searchable()->native(false),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label(__('Mètode de pagament'))
                    ->options(translated_labels(CampusEnrollment::PAYMENT_METHODS))
                    ->native(false),
            ])
            ->actions([
                Action::make('confirmar_pagament')
                    ->label(__('✓ Confirmar pagament'))
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record) => $record->status === 'pending' && $record->isManualPayment())
                    ->requiresConfirmation()
                    ->modalHeading(__('Confirmar recepció del pagament'))
                    ->modalDescription(fn($record) => __('Confirmar que s\'ha rebut el pagament de :name?', ['name' => $record->full_name]))
                    ->action(fn($record) => $record->update(['status' => 'paid', 'paid_at' => now()]))
                    ->successNotificationTitle(__('Pagament confirmat')),

                Action::make('registrar_devolucio')
                    ->label(__('↩ Devolució'))
                    ->color('warning')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn($record) => in_array($record->status, ['paid', 'confirmed']))
                    ->modalHeading(fn($record) => __('Devolució') . ' — ' . $record->full_name)
                    ->modalDescription(fn($record) => $record->payment_method === 'stripe'
                        ? __('⚡ Stripe: el reemborsament s\'enviarà automàticament a la targeta.')
                        : __('📋 Manual: el reemborsament s\'ha de fer externament pel mateix canal de pagament.'))
                    ->form(fn($record) => [
                        TextInput::make('refunded_amount')
                            ->label(__('Import a retornar (€)'))
                            ->numeric()
                            ->default(fn() => $record->amount)
                            ->minValue(0.01)
                            ->maxValue(fn() => $record->amount)
                            ->suffix('€')
                            ->required()
                            ->helperText(__('Import original: :amount € — podeu reduir-lo per devolucions parcials.', ['amount' => $record->amount])),

                        Textarea::make('refund_notes')
                            ->label(__('Observació interna (opcional)'))
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder(__('Motiu de la devolució, referència bancària, etc.')),
                    ])
                    ->action(function ($record, array $data) {
                        $refundedAmount = (float) $data['refunded_amount'];
                        $isStripe       = $record->payment_method === 'stripe';
                        $stripeRefundId = null;

                        // ── Stripe: reemborsament automàtic ──────────────────
                        if ($isStripe && $record->stripe_payment_intent) {
                            try {
                                Stripe::setApiKey(config('services.stripe.secret'));
                                $refund = StripeRefund::create([
                                    'payment_intent' => $record->stripe_payment_intent,
                                    'amount'         => (int) round($refundedAmount * 100), // cents
                                ]);
                                $stripeRefundId = $refund->id;
                            } catch (\Stripe\Exception\ApiErrorException $e) {
                                Notification::make()
                                    ->title(__('Error Stripe'))
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }

                        // ── Desar i enviar correu ────────────────────────────
                        $record->update([
                            'status'          => 'refunded',
                            'refunded_amount' => $refundedAmount,
                            'refunded_at'     => now(),
                            'refund_notes'    => $data['refund_notes'] ?? null,
                            'stripe_refund_id'=> $stripeRefundId,
                        ]);

                        $email = $record->student?->email ?? $record->email;
                        Mail::to($email)->send(new RefundConfirmationMail($record, $isStripe));

                        Notification::make()
                            ->title(__('Devolució registrada'))
                            ->body(__('S\'ha processat la devolució de :amount € i s\'ha notificat l\'alumne.', ['amount' => $refundedAmount]))
                            ->success()
                            ->send();
                    }),

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
        return (string) CampusEnrollment::where('tenant_id', current_tenant()?->id)->where('status', 'pending')->count() ?: null;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit'   => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
