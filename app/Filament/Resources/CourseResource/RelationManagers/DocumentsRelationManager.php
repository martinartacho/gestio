<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Models\CampusDocument;
use App\Models\CampusTeacher;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $title = 'Documents';
    protected static \BackedEnum|string|null $icon = 'heroicon-o-paper-clip';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Títol')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Textarea::make('description')
                ->label('Descripció')
                ->rows(2)
                ->columnSpanFull(),

            Select::make('type')
                ->label('Tipus')
                ->options(CampusDocument::TYPES)
                ->required()
                ->live()
                ->default('file'),

            Select::make('status')
                ->label('Estat')
                ->options(CampusDocument::STATUSES)
                ->default('active')
                ->required(),

            FileUpload::make('file_path')
                ->label('Fitxer')
                ->disk('local')
                ->directory('campus/documents')
                ->acceptedFileTypes(CampusDocument::ACCEPTED_MIMES)
                ->maxSize(CampusDocument::MAX_FILE_SIZE_KB)
                ->storeFileNamesIn('file_name')
                ->columnSpanFull()
                ->visible(fn($get) => $get('type') === 'file')
                ->required(fn($get) => $get('type') === 'file'),

            TextInput::make('url')
                ->label('URL')
                ->url()
                ->placeholder('https://...')
                ->columnSpanFull()
                ->visible(fn($get) => $get('type') === 'url')
                ->required(fn($get) => $get('type') === 'url'),

            Select::make('teacher_id')
                ->label('Professor/a')
                ->options(
                    CampusTeacher::where('status', 'active')
                        ->get()
                        ->mapWithKeys(fn($t) => [$t->id => $t->full_name])
                )
                ->searchable()
                ->nullable(),

            Select::make('visibility')
                ->label('Visibilitat')
                ->options(CampusDocument::VISIBILITIES)
                ->default('enrolled')
                ->required(),

            Toggle::make('inherit_to_editions')
                ->label('Heretar als cursos fills')
                ->default(false),

            TextInput::make('sort_order')
                ->label('Ordre')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Títol')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipus')
                    ->badge()
                    ->formatStateUsing(fn($state) => CampusDocument::TYPES[$state] ?? $state)
                    ->color(fn($state) => $state === 'file' ? 'gray' : 'info'),

                Tables\Columns\TextColumn::make('visibility')
                    ->label('Visibilitat')
                    ->badge()
                    ->formatStateUsing(fn($state) => CampusDocument::VISIBILITIES[$state] ?? $state)
                    ->color(fn($state) => match($state) {
                        'public'   => 'success',
                        'enrolled' => 'info',
                        'private'  => 'gray',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estat')
                    ->badge()
                    ->formatStateUsing(fn($state) => CampusDocument::STATUSES[$state] ?? $state)
                    ->color(fn($state) => $state === 'active' ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label('Mida')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('not_available_reason')
                    ->label('Accés alumnes')
                    ->getStateUsing(function (CampusDocument $r): string {
                        $r->loadMissing('course');
                        return $r->not_available_reason ?? '✓ Accessible';
                    })
                    ->color(fn(CampusDocument $r): string => $r->not_available_reason ? 'warning' : 'success')
                    ->badge(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipus')
                    ->options(CampusDocument::TYPES),

                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Visibilitat')
                    ->options(CampusDocument::VISIBILITIES),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Action::make('download')
                    ->label('Descarregar')
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
            ]);
    }
}
