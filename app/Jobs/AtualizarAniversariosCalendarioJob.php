<?php

namespace App\Jobs;

use App\Models\EventoCalendario;
use App\Models\Membro;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AtualizarAniversariosCalendarioJob implements ShouldQueue
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
        $anoAtual = Carbon::now()->year;
        
        // Remover eventos de aniversário do ano atual
        EventoCalendario::where('tipo', 'aniversario')
            ->whereYear('data_inicio', $anoAtual)
            ->delete();
        
        Log::info("Eventos de aniversário do ano {$anoAtual} removidos.");
        
        // Buscar todos os membros ativos com data de nascimento
        $membros = Membro::where('status', 'ativo')
            ->whereNotNull('data_nascimento')
            ->with('assembleia')
            ->get();
        
        $eventosInseridos = 0;
        
        foreach ($membros as $membro) {
            try {
                $dataAniversario = Carbon::createFromFormat('Y-m-d', $anoAtual . '-' . 
                    $membro->data_nascimento->format('m-d'));
                
                EventoCalendario::create([
                    'titulo' => "Aniversário de {$membro->nome_completo}",
                    'descricao' => "Aniversário do membro {$membro->nome_completo}",
                    'data_inicio' => $dataAniversario,
                    'data_fim' => $dataAniversario,
                    'hora_inicio' => '00:00:00',
                    'hora_fim' => '23:59:59',
                    'tipo' => 'aniversario',
                    'local' => $membro->assembleia->nome ?? '',
                    'assembleia_id' => $membro->assembleia_id,
                    'publico' => true,
                    'criado_por' => null, // Sistema
                ]);
                
                $eventosInseridos++;
                
            } catch (\Exception $e) {
                Log::error("Erro ao criar evento de aniversário para {$membro->nome_completo}: " . $e->getMessage());
            }
        }
        
        Log::info("Job AtualizarAniversariosCalendarioJob executado. {$eventosInseridos} eventos de aniversário criados para {$anoAtual}.");
    }
}
