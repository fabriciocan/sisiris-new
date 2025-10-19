<?php

namespace App\Filament\Resources\Jurisdicaos;

use App\Filament\Resources\Jurisdicaos\Pages\CreateJurisdicao;
use App\Filament\Resources\Jurisdicaos\Pages\EditJurisdicao;
use App\Filament\Resources\Jurisdicaos\Pages\ListJurisdicaos;
use App\Filament\Resources\Jurisdicaos\Schemas\JurisdicaoForm;
use App\Filament\Resources\Jurisdicaos\Tables\JurisdicaosTable;
use App\Models\Jurisdicao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class JurisdicaoResource extends Resource
{
    protected static ?string $model = Jurisdicao::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Jurisdições';
    
    protected static ?string $modelLabel = 'Jurisdição';
    
    protected static ?string $pluralModelLabel = 'Jurisdições';
    
    protected static ?int $navigationSort = 1;
    
    // Define a URL personalizada singular
    protected static ?string $slug = 'jurisdicoes';

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->hasRole('membro_jurisdicao');
    }

    public static function form(Schema $schema): Schema
    {
        return JurisdicaoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JurisdicaosTable::configure($table);
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
            'index' => ListJurisdicaos::route('/'),
            'create' => CreateJurisdicao::route('/create'),
            'edit' => EditJurisdicao::route('/{record}/edit'),
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
