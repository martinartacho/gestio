<?php

namespace App\Filament\Resources\CampusStudents\Tables;

use App\Mail\Campus\EmailVerificationMail;
use App\Models\CampusStudent;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class CampusStudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Alumne')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable('last_name')
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Correu')
                    ->searchable()
                    ->copyable(),

                IconColumn::make('email_verified_at')
                    ->label('Verificat')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn($record) => $record->hasVerifiedEmail()),

                IconColumn::make('suspended_at')
                    ->label('Suspès')
                    ->boolean()
                    ->trueIcon('heroicon-o-no-symbol')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->getStateUsing(fn($record) => $record->isSuspended()),

                TextColumn::make('enrollments_count')
                    ->label('Matrícules')
                    ->counts('enrollments')
                    ->badge()->color('primary'),

                // Indicador de sospita: cancel·lacions recents
                TextColumn::make('recent_cancellations')
                    ->label('⚠ Cancel·lac. 24h')
                    ->getStateUsing(fn($record) => $record->enrollments()
                        ->where('status', 'cancelled')
                        ->where('updated_at', '>=', now()->subHours(24))
                        ->count()
                    )
                    ->badge()
                    ->color(fn($state) => match(true) {
                        $state >= 3 => 'danger',
                        $state >= 1 => 'warning',
                        default     => 'gray',
                    }),

                TextColumn::make('phone')->label('Telèfon')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Registrat')->date('d/m/Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Verificació email')
                    ->nullable()
                    ->trueLabel('Verificats')
                    ->falseLabel('Sense verificar'),

                TernaryFilter::make('suspended_at')
                    ->label('Suspensió')
                    ->nullable()
                    ->trueLabel('Suspesos')
                    ->falseLabel('Actius'),

                Filter::make('suspicious')
                    ->label('Sospitosos (≥3 cancel·lacions en 24h)')
                    ->query(fn(Builder $q) => $q->whereHas('enrollments', fn($sub) =>
                        $sub->where('status', 'cancelled')
                            ->where('updated_at', '>=', now()->subHours(24)),
                        '>=', 3
                    )),
            ])
            ->recordActions([
                Action::make('verify_email')
                    ->label('✓ Verificar email')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn($record) => ! $record->hasVerifiedEmail())
                    ->requiresConfirmation()
                    ->modalHeading('Verificar email manualment')
                    ->modalDescription(fn($record) => "Confirmar email de {$record->full_name} ({$record->email}) sense que l'alumne faci clic a l'enllaç?")
                    ->action(function ($record) {
                        $record->update(['email_verified_at' => now()]);
                        Notification::make()->title('Email verificat manualment')->success()->send();
                    }),

                Action::make('resend_verification')
                    ->label('Reenviar verificació')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->visible(fn($record) => ! $record->hasVerifiedEmail())
                    ->action(function ($record) {
                        Mail::to($record->email)->send(new EmailVerificationMail($record));
                        Notification::make()->title('Correu reenviat a ' . $record->email)->success()->send();
                    }),

                Action::make('suspend')
                    ->label('Suspendre')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn($record) => ! $record->isSuspended())
                    ->form([
                        Textarea::make('suspension_reason')
                            ->label('Motiu de la suspensió')
                            ->required()
                            ->rows(2)
                            ->maxLength(300),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'suspended_at'       => now(),
                            'suspension_reason'  => $data['suspension_reason'],
                        ]);
                        Notification::make()->title('Alumne suspès')->warning()->send();
                    }),

                Action::make('unsuspend')
                    ->label('Aixecar suspensió')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->isSuspended())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['suspended_at' => null, 'suspension_reason' => null]);
                        Notification::make()->title('Suspensió aixecada')->success()->send();
                    }),

                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
