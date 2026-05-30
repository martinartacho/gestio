<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlockedIpResource\Pages;
use App\Models\BlockedIp;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BlockedIpResource extends Resource
{
    protected static ?string $model = BlockedIp::class;

    public static function getNavigationIcon(): string   { return 'heroicon-o-shield-exclamation'; }
    public static function getNavigationLabel(): string  { return 'IPs bloquejades'; }
    public static function getNavigationGroup(): string  { return __('site.treasury_group'); }
    public static function getModelLabel(): string       { return 'IP bloquejada'; }
    public static function getPluralModelLabel(): string { return 'IPs bloquejades'; }
    public static function getNavigationSort(): int      { return 10; }

    public static function canAccess(): bool                                       { return app(\App\Settings\SettingStore::class)->get('campus_enabled', true) && (auth()->user()?->hasPermissionTo('enrollments.edit') ?? false); }
    public static function canCreate(): bool                                       { return auth()->user()?->hasPermissionTo('enrollments.edit') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool   { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->hasPermissionTo('enrollments.edit') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('ip')
                ->label('Adreça IP')
                ->placeholder('192.168.1.1 o 2001:db8::1')
                ->required()
                ->maxLength(45)
                ->rules(['ip']),

            Textarea::make('reason')
                ->label('Motiu del bloqueig')
                ->rows(2)
                ->maxLength(300),

            DateTimePicker::make('expires_at')
                ->label('Caduca el (buit = permanent)')
                ->native(false)
                ->helperText('Deixeu en blanc per a un bloqueig permanent.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ip')
                    ->label('Adreça IP')
                    ->fontFamily('mono')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Motiu')
                    ->limit(50)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Caduca')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Permanent')
                    ->color(fn($state) => $state && now()->gt($state) ? 'danger' : 'gray'),

                Tables\Columns\IconColumn::make('active')
                    ->label('Actiu')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->isActive()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bloqueig')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                DeleteAction::make()->label('Desbloquejar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Desbloquejar seleccionades'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBlockedIps::route('/'),
            'create' => Pages\CreateBlockedIp::route('/create'),
        ];
    }
}
