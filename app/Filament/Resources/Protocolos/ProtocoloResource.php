<?php

namespace App\Filament\Resources\Protocolos;

use App\Filament\Resources\Protocolos\Pages;
use App\Filament\Resources\Protocolos\Pages\CreateProtocolo;
use App\Filament\Resources\Protocolos\Pages\ApproveMaioridadeProtocolo;
use App\Filament\Resources\Protocolos\Pages\ApproveIniciacaoProtocolo;
use App\Filament\Resources\Protocolos\Pages\EditProtocolo;
use App\Filament\Resources\Protocolos\Pages\ListProtocolos;
use App\Filament\Resources\Protocolos\Schemas\ProtocoloForm;
use App\Filament\Resources\Protocolos\Tables\ProtocolosTable;
use App\Filament\Resources\Protocolos\RelationManagers\AnexosRelationManager;
use App\Filament\Resources\Protocolos\RelationManagers\TaxasRelationManager;
use App\Filament\Resources\Protocolos\RelationManagers\HistoricoRelationManager;
use App\Models\Protocolo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProtocoloResource extends Resource
{
    protected static ?string $model = Protocolo::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Protocolos';
    
    protected static ?string $modelLabel = 'Protocolo';
    
    protected static ?string $pluralModelLabel = 'Protocolos';
    
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        // Admin jurisdição e admin assembleia podem acessar
        return $user && ($user->hasRole('membro_jurisdicao') || $user->hasRole('admin_assembleia'));
    }

    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        // Admin assembleia e membro jurisdição podem criar protocolos
        return $user && ($user->hasRole('admin_assembleia') || $user->hasRole('membro_jurisdicao'));
    }

    public static function form(Schema $schema): Schema
    {
        return ProtocoloForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProtocolosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            HistoricoRelationManager::class,
            AnexosRelationManager::class,
            TaxasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProtocolos::route('/'),
            'create' => CreateProtocolo::route('/create'),

            // Afastamento
            'create-afastamento' => Pages\Afastamento\CreateAfastamentoProtocolo::route('/afastamento/create'),
            'approve-afastamento' => Pages\Afastamento\ApproveAfastamentoProtocolo::route('/{record}/afastamento/approve'),

            // Maioridade
            'approve-maioridade' => ApproveMaioridadeProtocolo::route('/{record}/approve-maioridade'),

            // Iniciação
            'approve-iniciacao' => ApproveIniciacaoProtocolo::route('/{record}/approve-iniciacao'),

            'edit' => EditProtocolo::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        /** @var User|null $user */
        $user = Auth::user();
        
        // Admin jurisdição vê todos os protocolos
        // Admin assembleia vê apenas protocolos da sua assembleia
        if ($user && $user->hasRole('admin_assembleia') && $user->membro) {
            $query->where('assembleia_id', $user->membro->assembleia_id);
        }
        // membro_jurisdicao vê todos (não adiciona filtro)

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
