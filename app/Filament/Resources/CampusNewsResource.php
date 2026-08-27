<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampusNewsResource\Pages;
use App\Models\CampusNews;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{CheckboxList, DateTimePicker, RichEditor, Select, Textarea, TextInput};
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CampusNewsResource extends Resource
{
    protected static ?string $model = CampusNews::class;
    protected static ?string $slug  = 'noticies';
    protected static ?int    $navigationSort = 50;

    public static function getNavigationIcon(): string   { return 'heroicon-o-newspaper'; }
    public static function getNavigationLabel(): string  { return 'Notícies'; }
    public static function getNavigationGroup(): string  { return 'Notícies'; }
    public static function getModelLabel(): string       { return 'Notícia'; }
    public static function getPluralModelLabel(): string { return 'Notícies'; }

    public static function canAccess(): bool
    {
        $s = app(\App\Settings\SettingStore::class);
        return (bool) $s->get('noticies_enabled', true)
            && (auth()->user()?->hasPermissionTo('news.view') ?? false);
    }
    public static function canCreate(): bool                                       { return auth()->user()?->hasPermissionTo('news.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool   { return auth()->user()?->hasPermissionTo('news.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->hasPermissionTo('news.delete') ?? false; }
    public static function canDeleteAny(): bool                                    { return auth()->user()?->hasPermissionTo('news.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contingut')->columns(2)->schema([
                TextInput::make('title')
                    ->label('Títol')
                    ->required()->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('summary')
                    ->label('Resum')
                    ->helperText('Text curt que apareix als llistats i a la home.')
                    ->rows(2)->maxLength(500)
                    ->columnSpanFull(),

                RichEditor::make('body')
                    ->label('Cos de la notícia')
                    ->required()
                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'h3', 'undo', 'redo'])
                    ->columnSpanFull(),
            ]),

            Section::make('Etiquetes i publicació')->columns(2)->schema([
                CheckboxList::make('labels')
                    ->label('Etiquetes')
                    ->helperText('Una notícia pot tenir múltiples etiquetes.')
                    ->options(CampusNews::LABELS)
                    ->columns(3)
                    ->columnSpanFull(),

                Select::make('recipients')
                    ->label('Destinataris')
                    ->options(CampusNews::RECIPIENTS)
                    ->default('all')
                    ->required()
                    ->helperText('Qui pot veure aquesta notícia al portal públic.'),

                TextInput::make('version')
                    ->label('Versió')
                    ->placeholder('ex: v1.8.0')
                    ->maxLength(20),

                DateTimePicker::make('published_at')
                    ->label('Data de publicació')
                    ->helperText('Deixa en blanc per guardar com a esborrany.')
                    ->nullable()
                    ->seconds(false)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Títol')
                    ->searchable()->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('labels')
                    ->label('Etiquetes')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'campus'     => 'primary',
                        'associats'  => 'warning',
                        'tresoreria' => 'success',
                        'secretaria' => 'info',
                        'admin'      => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => CampusNews::LABELS[$state] ?? $state)
                    ->separator(','),

                Tables\Columns\TextColumn::make('recipients')
                    ->label('Destinataris')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'all'      => 'gray',
                        'private'  => 'warning',
                        'teachers' => 'success',
                        'students' => 'primary',
                        'members'  => 'info',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => CampusNews::RECIPIENTS[$state] ?? $state),

                Tables\Columns\TextColumn::make('version')
                    ->label('Versió')
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicada')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder('Esborrany'),

                Tables\Columns\IconColumn::make('published_at')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn($record) => $record->isPublished()),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordAction(null)
            ->actions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Eliminar'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar seleccionades'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCampusNews::route('/'),
            'create' => Pages\CreateCampusNews::route('/create'),
            'edit'   => Pages\EditCampusNews::route('/{record}/edit'),
        ];
    }
}
