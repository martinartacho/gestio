<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\CampusTeacher;
use Filament\Actions\{Action, BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{Select, TextInput, Textarea, Toggle};
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherResource extends Resource
{
    protected static ?string $model = CampusTeacher::class;
    protected static ?int    $navigationSort = 2;

    public static function getNavigationIcon(): string   { return 'heroicon-o-academic-cap'; }
    public static function getNavigationLabel(): string  { return __('site.teachers'); }
    public static function getNavigationGroup(): string  { return __('site.courses_group'); }
    public static function getModelLabel(): string       { return __('site.teacher'); }
    public static function getPluralModelLabel(): string { return __('site.teachers'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('site.teacher_personal'))->columns(2)->schema([
                TextInput::make('first_name')
                    ->label(__('site.teacher_firstname'))
                    ->required()->maxLength(100),

                TextInput::make('last_name')
                    ->label(__('site.teacher_lastname'))
                    ->required()->maxLength(100),

                TextInput::make('specialization')
                    ->label(__('site.teacher_spec'))
                    ->maxLength(150)->columnSpanFull(),

                Textarea::make('bio')
                    ->label(__('site.teacher_bio'))
                    ->rows(3)->columnSpanFull(),
            ]),

            Section::make(__('site.teacher_contact'))->columns(2)->schema([
                TextInput::make('email')
                    ->label(__('site.email'))
                    ->email()->maxLength(150),

                TextInput::make('phone')
                    ->label('Telèfon')
                    ->tel()->maxLength(20),

                Select::make('status')
                    ->label(__('site.teacher_status'))
                    ->options(CampusTeacher::STATUSES)
                    ->default('active')->required()->native(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('site.teacher'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable('last_name')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('specialization')
                    ->label(__('site.teacher_spec'))
                    ->searchable()->limit(40),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('site.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('courses_count')
                    ->label(__('site.courses'))
                    ->counts('courses')->badge()->color('primary'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('site.status'))
                    ->formatStateUsing(fn($state) => CampusTeacher::STATUSES[$state] ?? $state)
                    ->badge()
                    ->color(fn($state) => $state === 'active' ? 'success' : 'gray'),
            ])
            ->defaultSort('last_name')
            ->recordAction(null)
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options(CampusTeacher::STATUSES)
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

    public static function getNavigationBadge(): ?string
    {
        return (string) CampusTeacher::where('status', 'active')->count();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit'   => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
