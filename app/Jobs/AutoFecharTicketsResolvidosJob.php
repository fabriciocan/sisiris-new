<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutoFecharTicketsResolvidosJob implements ShouldQueue
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
        $agora = Carbon::now();
        
        // Buscar tickets resolvidos há mais de 7 dias
        $ticketsParaFechar = Ticket::where('status', 'resolvido')
            ->where('updated_at', '<', $agora->subDays(7))
            ->get();
        
        Log::info("Job AutoFecharTicketsResolvidosJob executado. Encontrados " . $ticketsParaFechar->count() . " tickets para fechar.");
        
        foreach ($ticketsParaFechar as $ticket) {
            try {
                $ticket->update([
                    'status' => 'fechado',
                    'data_fechamento' => $agora,
                ]);
                
                // Criar resposta automática informando o fechamento
                $ticket->respostas()->create([
                    'resposta' => 'Ticket fechado automaticamente após 7 dias sem atividade desde a resolução.',
                    'autor_id' => null, // Sistema
                    'tipo' => 'sistema',
                    'data_resposta' => $agora,
                ]);
                
                Log::info("Ticket {$ticket->id} fechado automaticamente.");
                
            } catch (\Exception $e) {
                Log::error("Erro ao fechar ticket {$ticket->id}: " . $e->getMessage());
            }
        }
    }
}
