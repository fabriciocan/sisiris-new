<?php

namespace App\Filament\Resources\Membros;

use App\Filament\Resources\Membros\Pages\CreateMembro;
use App\Filament\Resources\Membros\Pages\EditMembro;
use App\Filament\Resources\Membros\Pages\ListMembros;
use App\Filament\Resources\Membros\Schemas\MembroForm;
use App\Filament\Resources\Membros\Tables\MembrosTable;
use App\Models\Membro;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MembroResource extends Resource
{
    protected static ?string $model = Membro::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MembroForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembrosTable::configure($table);
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
            'index' => ListMembros::route('/'),
            'create' => CreateMembro::route('/create'),
            'edit' => EditMembro::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Se for admin de assembleia, filtrar apenas membros de sua assembleia
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin_assembleia')) {
            $userMembro = $user->membro;
            if ($userMembro) {
                $query->where('assembleia_id', $userMembro->assembleia_id);
            }
        }

        // Membro jurisdição vê todos os membros (não aplicar filtro)

        return $query;
    }
}
