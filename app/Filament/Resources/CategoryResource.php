<?php
// ══════════════════════════════════════════════════════════════════════════════
// app/Filament/Resources/CategoryResource.php
// ══════════════════════════════════════════════════════════════════════════════
namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\CampusCategory;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{Select, TextInput, Textarea, Toggle};
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = CampusCategory::class;
    protected static ?int    $navigationSort = 11;

    public static function getNavigationIcon(): string   { return 'heroicon-o-tag'; }
    public static function getNavigationLabel(): string  { return __('site.categories'); }
    public static function getNavigationGroup(): string  { return __('site.catalog'); }
    public static function getModelLabel(): string       { return __('site.category'); }
    public static function getPluralModelLabel(): string { return __('site.categories'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('site.category'))->columns(2)->schema([
                TextInput::make('name')
                    ->label(__('site.name'))
                    ->required()->maxLength(100)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label(__('site.description'))
                    ->rows(2)->columnSpanFull(),

                Select::make('color')
                    ->label(__('site.color'))
                    ->options(CampusCategory::COLORS)
                    ->default('blue')->required()->native(false),

                TextInput::make('order')
                    ->label(__('site.order'))
                    ->numeric()->default(0),

                Toggle::make('is_active')
                    ->label(__('site.active'))
                    ->default(true)->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('site.name'))
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('color')
                    ->label(__('site.color'))
                    ->badge()
                    ->color(fn($state) => $state),

                Tables\Columns\TextColumn::make('courses_count')
                    ->label(__('site.courses'))
                    ->counts('courses')->badge()->color('primary'),

                Tables\Columns\TextColumn::make('order')
                    ->label(__('site.order'))->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('site.active'))->boolean(),
            ])
            ->defaultSort('order')
            ->recordAction(null)
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
