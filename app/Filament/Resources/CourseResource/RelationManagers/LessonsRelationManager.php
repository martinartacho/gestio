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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
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
        return __('Lliçons LMS');
    }

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return (bool) app(\App\Settings\SettingStore::class)->get('lms_enabled', true);
    }

    // ─── Formulari ────────────────────────────────────────────────────────────

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('Capçalera'))->schema([
                TextInput::make('session_number')
                    ->label(__('Número de sessió'))
                    ->numeric()->minValue(1)->required(),
                TextInput::make('title')
                    ->label(__('Títol'))->required()->columnSpan(2),
                TextInput::make('subtitle')
                    ->label(__('Subtítol'))->columnSpan(2),
                TextInput::make('duration')
                    ->label(__('Durada'))
                    ->placeholder(__('ex. 1h 30min')),
                Select::make('status')
                    ->label(__('Estat'))
                    ->options(['draft' => __('Esborrany'), 'published' => __('Publicada')])
                    ->required()->default('draft'),
                TextInput::make('sort_order')
                    ->label(__('Ordre'))->numeric()->default(0),
            ])->columns(3),

            Section::make(__('Introducció'))->schema([
                Textarea::make('quote_text')
                    ->label(__('Cita'))
                    ->placeholder(__('"Lo bueno, si breve, dos veces bueno."'))
                    ->rows(2)->columnSpan(3),
                TextInput::make('quote_author')
                    ->label(__('Autor/a de la cita')),
                TextInput::make('quote_work')
                    ->label(__('Obra'))->columnSpan(2),
                Textarea::make('intro_text')
                    ->label(__('Paràgraf introductori'))
                    ->rows(3)->columnSpan(3),
            ])->columns(3)->collapsed(),

            Section::make(__('El tema d\'avui'))->schema([
                Textarea::make('topic_text')
                    ->label(__('Explicació del tema de la sessió'))
                    ->rows(4)->columnSpanFull(),
            ])->collapsed(),

            Section::make(__('Conceptes clau'))->schema([
                Repeater::make('concepts')
                    ->label('')
                    ->schema([
                        TextInput::make('icon')
                            ->label(__('Icona Tabler'))
                            ->placeholder(__('bulb, eye, user...'))
                            ->helperText(__('Nom de la icona sense "ti-"')),
                        TextInput::make('title')
                            ->label(__('Nom del concepte'))->required()->columnSpan(2),
                        Textarea::make('description')
                            ->label(__('Descripció'))->rows(2)->columnSpan(3),
                    ])->columns(3)
                    ->addActionLabel(__('Afegir concepte'))
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ])->collapsed(),

            Section::make(__('Texts (projecte i referència)'))->schema([
                Repeater::make('text_cards')
                    ->label('')
                    ->schema([
                        Select::make('type')
                            ->label(__('Tipus'))
                            ->options(['project' => __('Del projecte'), 'reference' => __('De referència')])
                            ->required(),
                        TextInput::make('title')
                            ->label(__('Títol del text'))->required()->columnSpan(2),
                        TextInput::make('author')
                            ->label(__('Autor/a'))->columnSpan(2),
                        TextInput::make('year')
                            ->label(__('Any')),
                        Textarea::make('extract')
                            ->label(__('Fragment (citat en cursiva)'))->rows(3)->columnSpan(3),
                        Textarea::make('analysis')
                            ->label(__('Anàlisi / Per què funciona'))->rows(3)->columnSpan(3),
                    ])->columns(3)
                    ->addActionLabel(__('Afegir text'))
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ])->collapsed(),

            Section::make(__('Comparació entre textos'))->schema([
                TextInput::make('comparison.left_label')
                    ->label(__('Etiqueta columna esquerra')),
                TextInput::make('comparison.right_label')
                    ->label(__('Etiqueta columna dreta')),
                Repeater::make('comparison.left_points')
                    ->label(__('Punts columna esquerra'))
                    ->simple(TextInput::make('point')->label(__('Punt')))
                    ->addActionLabel(__('Afegir punt')),
                Repeater::make('comparison.right_points')
                    ->label(__('Punts columna dreta'))
                    ->simple(TextInput::make('point')->label(__('Punt')))
                    ->addActionLabel(__('Afegir punt')),
            ])->columns(2)->collapsed(),

            Section::make(__('Reflexió (preguntes per al grup)'))->schema([
                Repeater::make('reflection_questions')
                    ->label('')
                    ->schema([
                        Textarea::make('question')
                            ->label(__('Pregunta'))->rows(2)->required()->columnSpanFull(),
                    ])
                    ->addActionLabel(__('Afegir pregunta'))
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ])->collapsed(),

            Section::make(__('Exercici pràctic'))->schema([
                TextInput::make('exercise.title')
                    ->label(__('Títol de l\'exercici'))->columnSpan(2),
                TextInput::make('exercise.duration')
                    ->label(__('Durada estimada'))
                    ->placeholder(__('ex. 20 min')),
                Textarea::make('exercise.statement')
                    ->label(__('Enunciat'))->rows(4)->columnSpan(3),
                Repeater::make('exercise.examples')
                    ->label(__('Opcions / Exemples'))
                    ->simple(TextInput::make('item')->label(__('Exemple')))
                    ->addActionLabel(__('Afegir exemple')),
                Repeater::make('exercise.tips')
                    ->label(__('Consells'))
                    ->simple(TextInput::make('tip')->label(__('Consell')))
                    ->addActionLabel(__('Afegir consell')),
                Textarea::make('exercise.demo_first_person')
                    ->label(__('Demo en 1a persona'))->rows(3),
                Textarea::make('exercise.demo_third_person')
                    ->label(__('Demo en 3a persona'))->rows(3),
            ])->columns(3)->collapsed(),

            Section::make(__('Preguntes interactives'))->schema([
                Repeater::make('questions')
                    ->label('')
                    ->schema([
                        TextInput::make('index')
                            ->label(__('Índex'))
                            ->numeric()
                            ->required()
                            ->helperText(__('Posició dins l\'array (0, 1, 2…)')),

                        Select::make('type')
                            ->label(__('Tipus de pregunta'))
                            ->options([
                                'open_text'            => __('Text obert (no avaluable)'),
                                'select_from_examples' => __('Selecció d\'exemples'),
                                'choice_one'           => __('Opció única (avaluable)'),
                                'choice_many'          => __('Múltiple opció (avaluable)'),
                                'yes_no'               => __('Sí / No (avaluable)'),
                            ])
                            ->required()
                            ->live()
                            ->columnSpan(2),

                        Select::make('block')
                            ->label(__('Bloc on apareix'))
                            ->options([
                                'reflection' => __('Reflexió'),
                                'exercise'   => __('Exercici'),
                                'quiz'       => __('Qüestionari'),
                            ])
                            ->required(),

                        Textarea::make('text')
                            ->label(__('Enunciat de la pregunta'))
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),

                        // Opcions (choice_one, choice_many, select_from_examples)
                        Repeater::make('options')
                            ->label(__('Opcions de resposta'))
                            ->simple(TextInput::make('option')->label(__('Opció')))
                            ->addActionLabel(__('Afegir opció'))
                            ->columnSpanFull()
                            ->hidden(fn (Get $get) => ! in_array($get('type'), ['choice_one', 'choice_many', 'select_from_examples'])),

                        // Resposta correcta única (choice_one, yes_no)
                        TextInput::make('correct_answer')
                            ->label(__('Resposta correcta'))
                            ->helperText(__('Per yes_no: "true" o "false". Per choice_one: el text exacte de l\'opció.'))
                            ->columnSpan(2)
                            ->hidden(fn (Get $get) => ! in_array($get('type'), ['choice_one', 'yes_no'])),

                        // Respostes correctes (choice_many)
                        Repeater::make('correct_answers')
                            ->label(__('Respostes correctes (múltiple)'))
                            ->simple(TextInput::make('answer')->label(__('Resposta')))
                            ->addActionLabel(__('Afegir resposta correcta'))
                            ->columnSpanFull()
                            ->hidden(fn (Get $get) => $get('type') !== 'choice_many'),

                        // Permetre opció personalitzada (select_from_examples)
                        Toggle::make('allow_custom')
                            ->label(__('Permetre opció personalitzada (camp de text lliure)'))
                            ->columnSpan(2)
                            ->hidden(fn (Get $get) => $get('type') !== 'select_from_examples'),

                        Toggle::make('required')
                            ->label(__('Obligatòria'))
                            ->default(false),

                        TextInput::make('points')
                            ->label(__('Punts'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(4)
                    ->addActionLabel(__('Afegir pregunta'))
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull()
                    ->itemLabel(fn (array $state): string =>
                        '[' . ($state['index'] ?? '?') . '] ' .
                        ($state['type'] ?? '—') . ' · ' .
                        mb_strimwidth($state['text'] ?? '', 0, 40, '…')
                    ),
            ])->collapsed()->collapsible(),

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
                    ->label(__('Títol'))
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('duration')
                    ->label(__('Durada'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Estat'))
                    ->badge()
                    ->color(fn (LmsLesson $r) => $r->status === 'published' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state) => $state === 'published' ? __('Publicada') : __('Esborrany')),
                Tables\Columns\TextColumn::make('progresses_count')
                    ->label(__('Completades'))
                    ->counts('progresses')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('responses_count')
                    ->label(__('Respostes'))
                    ->counts('responses')
                    ->badge()
                    ->color('info'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label(__('Veure'))
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
