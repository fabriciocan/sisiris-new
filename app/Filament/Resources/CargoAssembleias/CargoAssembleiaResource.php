<?php

namespace App\Filament\Resources\CargoAssembleias;

use App\Filament\Resources\CargoAssembleias\Pages\CreateCargoAssembleia;
use App\Filament\Resources\CargoAssembleias\Pages\EditCargoAssembleia;
use App\Filament\Resources\CargoAssembleias\Pages\ListCargoAssembleias;
use App\Filament\Resources\CargoAssembleias\Schemas\CargoAssembleiaForm;
use App\Filament\Resources\CargoAssembleias\Tables\CargoAssembleiasTable;
use App\Models\CargoAssembleia;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CargoAssembleiaResource extends Resource
{
    protected static ?string $model = CargoAssembleia::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Cargos Assembleia';
    
    protected static ?string $modelLabel = 'Cargo de Assembleia';
    
    protected static ?string $pluralModelLabel = 'Cargos de Assembleia';
    
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && ($user->hasRole('membro_jurisdicao') || $user->hasRole('admin_assembleia'));
    }

    public static function form(Schema $schema): Schema
    {
        return CargoAssembleiaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CargoAssembleiasTable::configure($table);
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
            'index' => ListCargoAssembleias::route('/'),
            'create' => CreateCargoAssembleia::route('/create'),
            'edit' => EditCargoAssembleia::route('/{record}/edit'),
        ];
    }
}
