<?php

namespace App\Jobs;

use App\Models\Protocolo;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class VerificarPrazosProtocolosJob implements ShouldQueue
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
        
        // Verificar protocolos pendentes há mais de 30 dias
        $protocolosVencidos = Protocolo::where('status', 'pendente')
            ->where('data_solicitacao', '<', $agora->subDays(30))
            ->with(['assembleia', 'solicitante'])
            ->get();
        
        Log::info("Job VerificarPrazosProtocolosJob executado. Encontrados " . $protocolosVencidos->count() . " protocolos vencidos.");
        
        foreach ($protocolosVencidos as $protocolo) {
            try {
                // Atualizar status para aguardando_documentos se necessário
                if ($protocolo->tipo === 'requerimento' && $protocolo->status === 'pendente') {
                    $protocolo->update(['status' => 'aguardando_documentos']);
                    
                    // Criar entrada no histórico
                    $protocolo->historico()->create([
                        'status_anterior' => 'pendente',
                        'status_novo' => 'aguardando_documentos',
                        'observacoes' => 'Status alterado automaticamente devido ao vencimento do prazo (30 dias)',
                        'data_mudanca' => $agora,
                        'user_id' => null, // Sistema
                    ]);
                }
                
                // Notificar responsáveis
                $adminsAssembleia = User::whereHas('membro', function ($query) use ($protocolo) {
                    $query->where('assembleia_id', $protocolo->assembleia_id);
                })
                // TODO: Adicionar verificação de permissão quando sistema estiver implementado
                // ->role('admin_assembleia')
                ->get();
                
                foreach ($adminsAssembleia as $admin) {
                    // $admin->notify(new ProtocoloVencidoNotification($protocolo));
                }
                
            } catch (\Exception $e) {
                Log::error("Erro ao processar protocolo vencido {$protocolo->numero_protocolo}: " . $e->getMessage());
            }
        }
        
        // Verificar protocolos em análise há mais de 15 dias
        $protocolosEmAnalise = Protocolo::where('status', 'em_analise')
            ->where('updated_at', '<', $agora->subDays(15))
            ->with(['assembleia', 'solicitante'])
            ->get();
        
        foreach ($protocolosEmAnalise as $protocolo) {
            try {
                // Notificar sobre necessidade de ação
                $adminsAssembleia = User::whereHas('membro', function ($query) use ($protocolo) {
                    $query->where('assembleia_id', $protocolo->assembleia_id);
                })
                // TODO: Adicionar verificação de permissão quando sistema estiver implementado
                // ->role('admin_assembleia')
                ->get();
                
                foreach ($adminsAssembleia as $admin) {
                    // $admin->notify(new ProtocoloAnaliseAtrasadaNotification($protocolo));
                }
                
            } catch (\Exception $e) {
                Log::error("Erro ao notificar protocolo em análise atrasado {$protocolo->numero_protocolo}: " . $e->getMessage());
            }
        }
    }
}
