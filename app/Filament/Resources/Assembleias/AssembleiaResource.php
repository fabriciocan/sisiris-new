<?php

namespace App\Filament\Resources\Assembleias;

use App\Filament\Resources\Assembleias\Pages\CreateAssembleia;
use App\Filament\Resources\Assembleias\Pages\EditAssembleia;
use App\Filament\Resources\Assembleias\Pages\ListAssembleias;
use App\Filament\Resources\Assembleias\RelationManagers\MembrosRelationManager;
use App\Filament\Resources\Assembleias\RelationManagers\CargosRelationManager;
use App\Filament\Resources\Assembleias\Schemas\AssembleiaForm;
use App\Filament\Resources\Assembleias\Tables\AssembleiasTable;
use App\Models\Assembleia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssembleiaResource extends Resource
{
    protected static ?string $model = Assembleia::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Assembleias';
    
    protected static ?string $modelLabel = 'Assembleia';
    
    protected static ?string $pluralModelLabel = 'Assembleias';
    
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return AssembleiaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssembleiasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // MembrosRelationManager::class,
            // CargosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssembleias::route('/'),
            'create' => CreateAssembleia::route('/create'),
            'edit' => EditAssembleia::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
