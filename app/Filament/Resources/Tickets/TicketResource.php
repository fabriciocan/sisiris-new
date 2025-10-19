<?php

namespace App\Filament\Resources\Tickets;

use App\Filament\Resources\Tickets\Pages\CreateTicket;
use App\Filament\Resources\Tickets\Pages\EditTicket;
use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Filament\Resources\Tickets\Schemas\TicketForm;
use App\Filament\Resources\Tickets\Tables\TicketsTable;
use App\Models\Ticket;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Tickets';
    
    protected static ?string $modelLabel = 'Ticket';
    
    protected static ?string $pluralModelLabel = 'Tickets';
    
    protected static string|UnitEnum|null $navigationGroup = 'Sistema de Tickets';
    
    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        // Todos os usuários autenticados podem acessar tickets
        return $user !== null;
    }

    public static function form(Schema $schema): Schema
    {
        return TicketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Tickets\RelationManagers\TicketRespostasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'edit' => EditTicket::route('/{record}/edit'),
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
        
        if (!$user) return $query;
        
        // Admin jurisdição vê todos os tickets
        if ($user->hasRole('membro_jurisdicao')) {
            return $query;
        }
        
        // Admin assembleia vê apenas tickets da sua assembleia
        if ($user->hasRole('admin_assembleia') && $user->membro) {
            return $query->where('assembleia_id', $user->membro->assembleia_id);
        }
        
        // Usuários normais veem apenas tickets que criaram ou estão atribuídos a eles
        return $query->where(function ($q) use ($user) {
            $q->where('solicitante_id', $user->id)
              ->orWhere('responsavel_id', $user->id);
        });
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
