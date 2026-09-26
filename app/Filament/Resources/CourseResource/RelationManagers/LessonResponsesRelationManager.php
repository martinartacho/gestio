<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Models\LmsLessonResponse;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LessonResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'lmsCertificates'; // placeholder; query override al baix

    protected static \BackedEnum|string|null $icon = 'heroicon-o-chat-bubble-left-right';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Respostes alumnes');
    }

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return (bool) app(\App\Settings\SettingStore::class)->get('lms_enabled', true);
    }

    // ─── Sobreescriure la query per mostrar totes les respostes del curs ──────

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return LmsLessonResponse::query()
            ->whereHas('lesson', fn ($q) => $q->where('course_id', $this->getOwnerRecord()->id))
            ->with(['student', 'lesson'])
            ->latest('submitted_at');
    }

    // ─── Formulari (buit — no creem respostes manualment) ────────────────────

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    // ─── Taula ────────────────────────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_index')
            ->paginated([25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('student.full_name')
                    ->label(__('Alumne/a'))
                    ->searchable(['campus_students.first_name', 'campus_students.last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('lesson.session_number')
                    ->label(__('Sessió'))
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('question_index')
                    ->label(__('Pregunta #'))
                    ->badge()
                    ->color('indigo'),

                Tables\Columns\TextColumn::make('question_type')
                    ->label(__('Tipus'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'yes_no'               => 'success',
                        'choice_one'           => 'info',
                        'choice_many'          => 'warning',
                        'open_text'            => 'gray',
                        'select_from_examples' => 'gray',
                        default                => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'yes_no'               => __('Sí/No'),
                        'choice_one'           => __('Única'),
                        'choice_many'          => __('Múltiple'),
                        'open_text'            => __('Text obert'),
                        'select_from_examples' => __('Exemples'),
                        default                => $state,
                    }),

                Tables\Columns\TextColumn::make('display_value')
                    ->label(__('Resposta'))
                    ->getStateUsing(fn (LmsLessonResponse $r) => $r->getDisplayValue())
                    ->limit(60)
                    ->tooltip(fn (LmsLessonResponse $r) => $r->getDisplayValue()),

                Tables\Columns\TextColumn::make('score')
                    ->label(__('Punts'))
                    ->badge()
                    ->color(fn ($state) => $state === null ? 'gray' : ($state > 0 ? 'success' : 'danger'))
                    ->formatStateUsing(fn ($state) => $state === null ? '—' : number_format($state, 2)),

                Tables\Columns\IconColumn::make('auto_graded')
                    ->label(__('Auto'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label(__('Enviada'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lesson')
                    ->label(__('Sessió'))
                    ->relationship('lesson', 'session_number')
                    ->getOptionLabelFromRecordUsing(fn ($r) => __('Sessió :number', ['number' => $r->session_number]) . ' · ' . $r->title),

                Tables\Filters\SelectFilter::make('question_type')
                    ->label(__('Tipus de pregunta'))
                    ->options([
                        'open_text'            => __('Text obert'),
                        'select_from_examples' => __('Exemples'),
                        'choice_one'           => __('Opció única'),
                        'choice_many'          => __('Múltiple opció'),
                        'yes_no'               => __('Sí / No'),
                    ]),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
