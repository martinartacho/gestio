<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Models\LmsLesson;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';
    protected static \BackedEnum|string|null $icon = 'heroicon-o-academic-cap';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'Lliçons LMS';
    }

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return (bool) app(\App\Settings\SettingStore::class)->get('lms_enabled', true);
    }

    // ─── Formulari ────────────────────────────────────────────────────────────

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Capçalera')->schema([
                TextInput::make('session_number')
                    ->label('Número de sessió')
                    ->numeric()->minValue(1)->required(),
                TextInput::make('title')
                    ->label('Títol')->required()->columnSpan(2),
                TextInput::make('subtitle')
                    ->label('Subtítol')->columnSpan(2),
                TextInput::make('duration')
                    ->label('Durada')
                    ->placeholder('ex. 1h 30min'),
                Select::make('status')
                    ->label('Estat')
                    ->options(['draft' => 'Esborrany', 'published' => 'Publicada'])
                    ->required()->default('draft'),
                TextInput::make('sort_order')
                    ->label('Ordre')->numeric()->default(0),
            ])->columns(3),

            Section::make('Introducció')->schema([
                Textarea::make('quote_text')
                    ->label('Cita')
                    ->placeholder('"Lo bueno, si breve, dos veces bueno."')
                    ->rows(2)->columnSpan(3),
                TextInput::make('quote_author')
                    ->label('Autor/a de la cita'),
                TextInput::make('quote_work')
                    ->label('Obra')->columnSpan(2),
                Textarea::make('intro_text')
                    ->label('Paràgraf introductori')
                    ->rows(3)->columnSpan(3),
            ])->columns(3)->collapsed(),

            Section::make('El tema d\'avui')->schema([
                Textarea::make('topic_text')
                    ->label('Explicació del tema de la sessió')
                    ->rows(4)->columnSpanFull(),
            ])->collapsed(),

            Section::make('Conceptes clau')->schema([
                Repeater::make('concepts')
                    ->label('')
                    ->schema([
                        TextInput::make('icon')
                            ->label('Icona Tabler')
                            ->placeholder('bulb, eye, user...')
                            ->helperText('Nom de la icona sense "ti-"'),
                        TextInput::make('title')
                            ->label('Nom del concepte')->required()->columnSpan(2),
                        Textarea::make('description')
                            ->label('Descripció')->rows(2)->columnSpan(3),
                    ])->columns(3)
                    ->addActionLabel('Afegir concepte')
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ])->collapsed(),

            Section::make('Texts (projecte i referència)')->schema([
                Repeater::make('text_cards')
                    ->label('')
                    ->schema([
                        Select::make('type')
                            ->label('Tipus')
                            ->options(['project' => 'Del projecte', 'reference' => 'De referència'])
                            ->required(),
                        TextInput::make('title')
                            ->label('Títol del text')->required()->columnSpan(2),
                        TextInput::make('author')
                            ->label('Autor/a')->columnSpan(2),
                        TextInput::make('year')
                            ->label('Any'),
                        Textarea::make('extract')
                            ->label('Fragment (citat en cursiva)')->rows(3)->columnSpan(3),
                        Textarea::make('analysis')
                            ->label('Anàlisi / Per què funciona')->rows(3)->columnSpan(3),
                    ])->columns(3)
                    ->addActionLabel('Afegir text')
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ])->collapsed(),

            Section::make('Comparació entre textos')->schema([
                TextInput::make('comparison.left_label')
                    ->label('Etiqueta columna esquerra'),
                TextInput::make('comparison.right_label')
                    ->label('Etiqueta columna dreta'),
                Repeater::make('comparison.left_points')
                    ->label('Punts columna esquerra')
                    ->simple(TextInput::make('point')->label('Punt'))
                    ->addActionLabel('Afegir punt'),
                Repeater::make('comparison.right_points')
                    ->label('Punts columna dreta')
                    ->simple(TextInput::make('point')->label('Punt'))
                    ->addActionLabel('Afegir punt'),
            ])->columns(2)->collapsed(),

            Section::make('Reflexió (preguntes per al grup)')->schema([
                Repeater::make('reflection_questions')
                    ->label('')
                    ->schema([
                        Textarea::make('question')
                            ->label('Pregunta')->rows(2)->required()->columnSpanFull(),
                    ])
                    ->addActionLabel('Afegir pregunta')
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ])->collapsed(),

            Section::make('Exercici pràctic')->schema([
                TextInput::make('exercise.title')
                    ->label('Títol de l\'exercici')->columnSpan(2),
                TextInput::make('exercise.duration')
                    ->label('Durada estimada')
                    ->placeholder('ex. 20 min'),
                Textarea::make('exercise.statement')
                    ->label('Enunciat')->rows(4)->columnSpan(3),
                Repeater::make('exercise.examples')
                    ->label('Opcions / Exemples')
                    ->simple(TextInput::make('item')->label('Exemple'))
                    ->addActionLabel('Afegir exemple'),
                Repeater::make('exercise.tips')
                    ->label('Consells')
                    ->simple(TextInput::make('tip')->label('Consell'))
                    ->addActionLabel('Afegir consell'),
                Textarea::make('exercise.demo_first_person')
                    ->label('Demo en 1a persona')->rows(3),
                Textarea::make('exercise.demo_third_person')
                    ->label('Demo en 3a persona')->rows(3),
            ])->columns(3)->collapsed(),

        ]);
    }

    // ─── Taula ────────────────────────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('session_number')
                    ->label('#')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Títol')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Durada')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estat')
                    ->badge()
                    ->color(fn (LmsLesson $r) => $r->status === 'published' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state) => $state === 'published' ? 'Publicada' : 'Esborrany'),
                Tables\Columns\TextColumn::make('progresses_count')
                    ->label('Completades')
                    ->counts('progresses')
                    ->badge()
                    ->color('gray'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Veure')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (LmsLesson $r) => route('lms.preview', $r->id))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
