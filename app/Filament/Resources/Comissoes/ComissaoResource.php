<?php

namespace App\Filament\Resources\Comissoes;

use App\Filament\Resources\Comissoes\Pages\CreateComissao;
use App\Filament\Resources\Comissoes\Pages\EditComissao;
use App\Filament\Resources\Comissoes\Pages\ListComissoes;
use App\Filament\Resources\Comissoes\RelationManagers\MembrosRelationManager;
use App\Filament\Resources\Comissoes\Schemas\ComissaoForm;
use App\Filament\Resources\Comissoes\Tables\ComissoesTable;
use App\Models\Comissao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ComissaoResource extends Resource
{
    protected static ?string $model = Comissao::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Comissões';
    
    protected static ?string $modelLabel = 'Comissão';
    
    protected static ?string $pluralModelLabel = 'Comissões';
    
    protected static ?int $navigationSort = 3;
    
    // Define a URL personalizada singular  
    protected static ?string $slug = 'comissoes';

    public static function form(Schema $schema): Schema
    {
        return ComissaoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComissoesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MembrosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComissoes::route('/'),
            'create' => CreateComissao::route('/create'),
            'edit' => EditComissao::route('/{record}/edit'),
        ];
    }
}
