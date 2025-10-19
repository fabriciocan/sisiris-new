<?php

namespace App\Services;

use App\Models\Ticket;
use Carbon\Carbon;

class TicketSLAService
{
    /**
     * Prazos por prioridade (em horas)
     */
    private const PRAZOS_SLA = [
        'urgente' => 4,
        'alta' => 24,
        'normal' => 48,
        'baixa' => 72,
    ];

    /**
     * Calcular prazo de SLA baseado na prioridade
     */
    public function calcularPrazoSLA(string $prioridade, Carbon $dataAbertura = null): Carbon
    {
        $dataBase = $dataAbertura ?? Carbon::now();
        $horas = self::PRAZOS_SLA[$prioridade] ?? self::PRAZOS_SLA['normal'];
        
        return $dataBase->addHours($horas);
    }

    /**
     * Verificar se ticket está próximo do vencimento (25% do tempo restante)
     */
    public function isProximoVencimento(Ticket $ticket): bool
    {
        if (!$ticket->data_abertura || in_array($ticket->status, ['resolvido', 'fechado', 'cancelado'])) {
            return false;
        }

        $prazoSLA = $this->calcularPrazoSLA($ticket->prioridade, $ticket->data_abertura);
        $tempoTotal = $ticket->data_abertura->diffInMinutes($prazoSLA);
        $tempoDecorrido = $ticket->data_abertura->diffInMinutes(Carbon::now());
        
        // Próximo do vencimento se passou 75% do tempo
        return ($tempoDecorrido / $tempoTotal) >= 0.75;
    }

    /**
     * Verificar se ticket está vencido
     */
    public function isVencido(Ticket $ticket): bool
    {
        if (!$ticket->data_abertura || in_array($ticket->status, ['resolvido', 'fechado', 'cancelado'])) {
            return false;
        }

        $prazoSLA = $this->calcularPrazoSLA($ticket->prioridade, $ticket->data_abertura);
        return Carbon::now()->isAfter($prazoSLA);
    }

    /**
     * Obter cor do badge baseado no status do SLA
     */
    public function getCorSLA(Ticket $ticket): string
    {
        if (in_array($ticket->status, ['resolvido', 'fechado'])) {
            return 'success';
        }

        if ($this->isVencido($ticket)) {
            return 'danger';
        }

        if ($this->isProximoVencimento($ticket)) {
            return 'warning';
        }

        return 'primary';
    }

    /**
     * Obter texto do status SLA
     */
    public function getStatusSLA(Ticket $ticket): string
    {
        if (in_array($ticket->status, ['resolvido', 'fechado'])) {
            return 'Concluído';
        }

        if ($this->isVencido($ticket)) {
            return 'Vencido';
        }

        if ($this->isProximoVencimento($ticket)) {
            return 'Próximo do Vencimento';
        }

        $prazoSLA = $this->calcularPrazoSLA($ticket->prioridade, $ticket->data_abertura);
        $horasRestantes = Carbon::now()->diffInHours($prazoSLA, false);
        
        if ($horasRestantes < 0) {
            return 'Vencido';
        }

        if ($horasRestantes < 1) {
            $minutosRestantes = Carbon::now()->diffInMinutes($prazoSLA, false);
            return "{$minutosRestantes}min restantes";
        }

        return "{$horasRestantes}h restantes";
    }

    /**
     * Obter tickets próximos do vencimento
     */
    public function getTicketsProximosVencimento(): \Illuminate\Database\Eloquent\Collection
    {
        $tickets = Ticket::whereIn('status', ['aberto', 'em_atendimento', 'aguardando_resposta'])
            ->with(['assembleia', 'solicitante'])
            ->get();

        return $tickets->filter(function ($ticket) {
            return $this->isProximoVencimento($ticket);
        });
    }

    /**
     * Obter tickets vencidos
     */
    public function getTicketsVencidos(): \Illuminate\Database\Eloquent\Collection
    {
        $tickets = Ticket::whereIn('status', ['aberto', 'em_atendimento', 'aguardando_resposta'])
            ->with(['assembleia', 'solicitante'])
            ->get();

        return $tickets->filter(function ($ticket) {
            return $this->isVencido($ticket);
        });
    }

    /**
     * Obter estatísticas de SLA
     */
    public function getEstatisticasSLA(): array
    {
        $tickets = Ticket::whereIn('status', ['aberto', 'em_atendimento', 'aguardando_resposta'])->get();
        
        $total = $tickets->count();
        $vencidos = $tickets->filter(fn($t) => $this->isVencido($t))->count();
        $proximosVencimento = $tickets->filter(fn($t) => $this->isProximoVencimento($t))->count();
        $emDia = $total - $vencidos - $proximosVencimento;

        return [
            'total' => $total,
            'em_dia' => $emDia,
            'proximos_vencimento' => $proximosVencimento,
            'vencidos' => $vencidos,
            'percentual_cumprimento' => $total > 0 ? round(($emDia / $total) * 100, 1) : 100,
        ];
    }
}