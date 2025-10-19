<?php

namespace App\Jobs;

use App\Services\TicketSLAService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class VerificarSLATickets implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $slaService = new TicketSLAService();
        
        // Verificar tickets próximos do vencimento
        $ticketsProximos = $slaService->getTicketsProximosVencimento();
        
        // Verificar tickets vencidos
        $ticketsVencidos = $slaService->getTicketsVencidos();
        
        if ($ticketsProximos->count() > 0 || $ticketsVencidos->count() > 0) {
            Log::info('Verificação de SLA realizada', [
                'tickets_proximos_vencimento' => $ticketsProximos->count(),
                'tickets_vencidos' => $ticketsVencidos->count(),
            ]);
            
            // Aqui seria implementado o sistema de notificações
            // Para admins sobre tickets próximos do vencimento ou vencidos
        }
        
        // Obter estatísticas gerais
        $estatisticas = $slaService->getEstatisticasSLA();
        
        Log::info('Estatísticas de SLA atualizadas', $estatisticas);
    }
}
