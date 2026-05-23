<?php

namespace App\Filament\Resources\CampusStudents;

use App\Filament\Resources\CampusStudents\Pages\CreateCampusStudent;
use App\Filament\Resources\CampusStudents\Pages\EditCampusStudent;
use App\Filament\Resources\CampusStudents\Pages\ListCampusStudents;
use App\Models\CampusStudent;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CampusStudentResource extends Resource
{
    protected static ?string $model = CampusStudent::class;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getNavigationIcon(): string   { return 'heroicon-o-academic-cap'; }
    public static function getNavigationLabel(): string  { return 'Alumnes'; }
    public static function getNavigationGroup(): string  { return __('site.treasury_group'); }
    public static function getModelLabel(): string       { return 'Alumne'; }
    public static function getPluralModelLabel(): string { return 'Alumnes'; }
    public static function getNavigationSort(): int      { return 5; }

    public static function canAccess(): bool                                       { return auth()->user()?->hasPermissionTo('enrollments.view')   ?? false; }
    public static function canCreate(): bool                                       { return auth()->user()?->hasPermissionTo('enrollments.create') ?? false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool   { return auth()->user()?->hasPermissionTo('enrollments.edit')   ?? false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->hasPermissionTo('enrollments.delete') ?? false; }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\CampusStudents\Schemas\CampusStudentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\CampusStudents\Tables\CampusStudentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCampusStudents::route('/'),
            'create' => CreateCampusStudent::route('/create'),
            'edit'   => EditCampusStudent::route('/{record}/edit'),
        ];
    }
}
