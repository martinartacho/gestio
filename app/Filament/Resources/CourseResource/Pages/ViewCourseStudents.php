<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use App\Models\CampusEnrollment;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViewCourseStudents extends ManageRelatedRecords
{
    protected static string $resource = CourseResource::class;
    protected static string $relationship = 'enrollments';
    public static function getNavigationLabel(): string
    {
        return 'Alumnes';
    }

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('student'))
            ->columns([
                Tables\Columns\TextColumn::make('student.full_name')
                    ->label('Alumne/a')
                    ->searchable(query: fn ($q, $s) => $q->whereHas('student', fn ($sq) => $sq->where('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%")))
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.email')
                    ->label('Correu')
                    ->copyable(),
                Tables\Columns\TextColumn::make('student.phone')
                    ->label('Telèfon')
                    ->placeholder('—'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estat')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger'  => 'cancelled',
                        'gray'    => 'refunded',
                    ])
                    ->formatStateUsing(fn ($state) => CampusEnrollment::STATUSES[$state] ?? $state),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Import')
                    ->money('EUR')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Pagat')
                    ->dateTime('d/m/Y')
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estat')
                    ->options(CampusEnrollment::STATUSES),
            ]);
    }
}
