<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\CampusCourse;
use App\Models\CampusDocument;
use App\Models\CampusTeacher;
use App\Settings\SettingStore;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends Resource
{
    protected static ?string $model = CampusDocument::class;
    protected static ?int    $navigationSort  = 10;

    public static function getNavigationIcon(): string   { return 'heroicon-o-paper-clip'; }
    public static function getNavigationGroup(): string  { return __('Campus'); }
    public static function getNavigationLabel(): string  { return __('Documents'); }
    public static function getModelLabel(): string       { return 'document'; }
    public static function getPluralModelLabel(): string { return 'documents'; }

    public static function canAccess(): bool
    {
        $store = app(SettingStore::class);
        return $store->get('campus_enabled', true)
            && $store->get('documents_enabled', true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('Informació bàsica'))->schema([
                TextInput::make('title')
                    ->label(__('Títol'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label(__('Descripció'))
                    ->rows(2)
                    ->columnSpanFull(),

                Select::make('type')
                    ->label(__('Tipus'))
                    ->options(translated_labels(CampusDocument::TYPES))
                    ->required()
                    ->live()
                    ->default('file'),

                Select::make('status')
                    ->label(__('Estat'))
                    ->options(translated_labels(CampusDocument::STATUSES))
                    ->default('active')
                    ->required(),
            ])->columns(2),

            Section::make(__('Fitxer'))
                ->schema([
                    FileUpload::make('file_path')
                        ->label(__('Fitxer'))
                        ->disk('local')
                        ->directory('campus/documents')
                        ->acceptedFileTypes(CampusDocument::ACCEPTED_MIMES)
                        ->maxSize(CampusDocument::MAX_FILE_SIZE_KB)
                        ->storeFileNamesIn('file_name')
                        ->columnSpanFull()
                        ->required(fn($get) => $get('type') === 'file'),
                ])
                ->visible(fn($get) => $get('type') === 'file'),

            Section::make(__('Enllaç extern'))
                ->schema([
                    TextInput::make('url')
                        ->label('URL')
                        ->url()
                        ->placeholder('https://www.youtube.com/watch?v=...')
                        ->columnSpanFull()
                        ->required(fn($get) => $get('type') === 'url'),
                ])
                ->visible(fn($get) => $get('type') === 'url'),

            Section::make(__('Assignació'))->schema([
                Select::make('course_id')
                    ->label(__('Curs'))
                    ->options(
                        CampusCourse::orderByDesc('start_date')
                            ->get()
                            ->mapWithKeys(fn($c) => [$c->id => "[{$c->code}] {$c->title}"])
                    )
                    ->searchable()
                    ->nullable()
                    ->live(),

                Select::make('teacher_id')
                    ->label(__('Professor/a'))
                    ->options(
                        CampusTeacher::where('status', 'active')
                            ->get()
                            ->mapWithKeys(fn($t) => [$t->id => $t->full_name])
                    )
                    ->searchable()
                    ->nullable(),

                Select::make('visibility')
                    ->label(__('Visibilitat'))
                    ->options(translated_labels(CampusDocument::VISIBILITIES))
                    ->default('enrolled')
                    ->required(),

                Toggle::make('inherit_to_editions')
                    ->label(__('Heretar als cursos fills'))
                    ->helperText(__('Si el curs és template, els fills també veuran aquest document.'))
                    ->default(false),
            ])->columns(2),

            Section::make(__('Condicions d\'accés'))->schema([
                DateTimePicker::make('available_from')
                    ->label(__('Disponible a partir de'))
                    ->nullable()
                    ->native(false),

                TextInput::make('session_number')
                    ->label(__('A partir de la sessió #'))
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->helperText(__('Deixar buit per a accés immediat.')),

                TextInput::make('sort_order')
                    ->label(__('Ordre'))
                    ->numeric()
                    ->default(0),
            ])->columns(3)->collapsed(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Títol'))
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('Tipus'))
                    ->badge()
                    ->formatStateUsing(fn($state) => __(CampusDocument::TYPES[$state] ?? $state))
                    ->color(fn($state) => $state === 'file' ? 'gray' : 'info'),

                Tables\Columns\TextColumn::make('course.title')
                    ->label(__('Curs'))
                    ->limit(30)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('visibility')
                    ->label(__('Visibilitat'))
                    ->badge()
                    ->formatStateUsing(fn($state) => __(CampusDocument::VISIBILITIES[$state] ?? $state))
                    ->color(fn($state) => match($state) {
                        'public'   => 'success',
                        'enrolled' => 'info',
                        'private'  => 'gray',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Estat'))
                    ->badge()
                    ->formatStateUsing(fn($state) => __(CampusDocument::STATUSES[$state] ?? $state))
                    ->color(fn($state) => $state === 'active' ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label(__('Mida'))
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('not_available_reason')
                    ->label(__('Accés alumnes'))
                    ->getStateUsing(function (CampusDocument $r): string {
                        $r->loadMissing('course');
                        return $r->not_available_reason ?? '✓ Accessible';
                    })
                    ->color(fn(CampusDocument $r): string => $r->not_available_reason ? 'warning' : 'success')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Creat'))
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Tipus'))
                    ->options(translated_labels(CampusDocument::TYPES)),

                Tables\Filters\SelectFilter::make('visibility')
                    ->label(__('Visibilitat'))
                    ->options(translated_labels(CampusDocument::VISIBILITIES)),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Estat'))
                    ->options(translated_labels(CampusDocument::STATUSES)),
            ])
            ->actions([
                Action::make('download')
                    ->label(__('Descarregar'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(CampusDocument $r) => $r->download_url)
                    ->openUrlInNewTab()
                    ->visible(fn(CampusDocument $r) => $r->download_url !== null),

                EditAction::make(),
                DeleteAction::make()
                    ->after(function (CampusDocument $record) {
                        if ($record->file_path) {
                            Storage::disk('local')->delete($record->file_path);
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit'   => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
