<?php

namespace App\Filament\Resources\CargoGrandeAssembleias;

use App\Filament\Resources\CargoGrandeAssembleias\Pages\CreateCargoGrandeAssembleia;
use App\Filament\Resources\CargoGrandeAssembleias\Pages\EditCargoGrandeAssembleia;
use App\Filament\Resources\CargoGrandeAssembleias\Pages\ListCargoGrandeAssembleias;
use App\Filament\Resources\CargoGrandeAssembleias\Schemas\CargoGrandeAssembleiaForm;
use App\Filament\Resources\CargoGrandeAssembleias\Tables\CargoGrandeAssembleiasTable;
use App\Models\CargoGrandeAssembleia;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CargoGrandeAssembleiaResource extends Resource
{
    protected static ?string $model = CargoGrandeAssembleia::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Cargos Grande Assembleia';
    
    protected static ?string $modelLabel = 'Cargo de Grande Assembleia';
    
    protected static ?string $pluralModelLabel = 'Cargos de Grande Assembleia';
    
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->hasRole('membro_jurisdicao');
    }

    public static function form(Schema $schema): Schema
    {
        return CargoGrandeAssembleiaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CargoGrandeAssembleiasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCargoGrandeAssembleias::route('/'),
            'create' => CreateCargoGrandeAssembleia::route('/create'),
            'edit' => EditCargoGrandeAssembleia::route('/{record}/edit'),
        ];
    }
}
