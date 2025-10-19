<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Ticket;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('numero_ticket')
                    ->label('Número do Ticket')
                    ->disabled()
                    ->dehydrated()
                    ->default(fn () => static::generateTicketNumber())
                    ->helperText('Gerado automaticamente no formato TKT-YYYY-NNN'),

                Select::make('assembleia_id')
                    ->label('Assembleia')
                    ->relationship('assembleia', 'nome')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(function () {
                        /** @var User|null $user */
                        $user = Auth::user();
                        // Se for admin_assembleia, pré-seleciona sua assembleia
                        if ($user && $user->hasRole('admin_assembleia') && $user->membro) {
                            return $user->membro->assembleia_id;
                        }
                        return null;
                    })
                    ->disabled(function () {
                        /** @var User|null $user */
                        $user = Auth::user();
                        // Admin assembleia tem campo bloqueado
                        return $user && $user->hasRole('admin_assembleia');
                    })
                    ->dehydrated(),

                Select::make('categoria')
                    ->label('Categoria')
                    ->options([
                        'duvida' => 'Dúvida',
                        'suporte_tecnico' => 'Suporte Técnico',
                        'financeiro' => 'Financeiro',
                        'ritual' => 'Ritual',
                        'evento' => 'Evento',
                        'administrativo' => 'Administrativo',
                        'outros' => 'Outros',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('comissao_id', static::getComissaoByCategoria($state))),

                Select::make('comissao_id')
                    ->label('Comissão Responsável')
                    ->relationship('comissao', 'nome')
                    ->searchable()
                    ->preload()
                    ->helperText('Será atribuída automaticamente baseada na categoria'),

                TextInput::make('assunto')
                    ->label('Assunto')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Descreva resumidamente o problema ou solicitação'),

                Textarea::make('descricao')
                    ->label('Descrição Detalhada')
                    ->required()
                    ->rows(4)
                    ->maxLength(2000)
                    ->placeholder('Descreva detalhadamente sua solicitação ou problema')
                    ->columnSpanFull(),

                Select::make('prioridade')
                    ->label('Prioridade')
                    ->options([
                        'baixa' => 'Baixa', 
                        'normal' => 'Normal', 
                        'alta' => 'Alta', 
                        'urgente' => 'Urgente'
                    ])
                    ->default('normal')
                    ->required()
                    ->helperText('Urgente: resposta em 4h | Alta: 24h | Normal: 48h | Baixa: 72h'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'aberto' => 'Aberto',
                        'em_atendimento' => 'Em Atendimento',
                        'aguardando_resposta' => 'Aguardando Resposta',
                        'resolvido' => 'Resolvido',
                        'fechado' => 'Fechado',
                        'cancelado' => 'Cancelado',
                    ])
                    ->default('aberto')
                    ->required()
                    ->disabled(function () {
                        /** @var User|null $user */
                        $user = Auth::user();
                        // Apenas admin pode alterar status
                        return !$user || (!$user->hasRole('membro_jurisdicao') && !$user->hasRole('admin_assembleia'));
                    }),

                Select::make('responsavel_id')
                    ->label('Responsável')
                    ->relationship('responsavel', 'name')
                    ->searchable()
                    ->placeholder('Selecione um responsável (opcional)')
                    ->visible(function () {
                        /** @var User|null $user */
                        $user = Auth::user();
                        // Apenas admin pode atribuir responsável
                        return $user && ($user->hasRole('membro_jurisdicao') || $user->hasRole('admin_assembleia'));
                    }),

                Hidden::make('solicitante_id')
                    ->default(Auth::id()),

                Hidden::make('data_abertura')
                    ->default(now()),
            ])
            ->columns(2);
    }

    /**
     * Gerar número automático do ticket
     */
    private static function generateTicketNumber(): string
    {
        $year = now()->year;
        $lastTicket = Ticket::where('numero_ticket', 'like', "TKT-{$year}-%")
            ->orderBy('numero_ticket', 'desc')
            ->first();

        if (!$lastTicket) {
            $nextNumber = 1;
        } else {
            $parts = explode('-', $lastTicket->numero_ticket);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('TKT-%d-%03d', $year, $nextNumber);
    }

    /**
     * Obter comissão baseada na categoria
     */
    private static function getComissaoByCategoria(?string $categoria): ?int
    {
        if (!$categoria) return null;

        $mapeamento = [
            'financeiro' => 'Financeira',
            'ritual' => 'Ritual',
            'evento' => 'Eventos',
            'administrativo' => 'Administrativa',
        ];

        $nomeComissao = $mapeamento[$categoria] ?? null;
        if (!$nomeComissao) return null;

        $comissao = \App\Models\Comissao::where('nome', 'like', "%{$nomeComissao}%")->first();
        return $comissao?->id;
    }
}
