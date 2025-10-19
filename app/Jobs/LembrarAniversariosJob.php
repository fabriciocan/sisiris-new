<?php

namespace App\Jobs;

use App\Models\Membro;
use App\Models\User;
use App\Notifications\AniversarioNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class LembrarAniversariosJob implements ShouldQueue
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
        $hoje = Carbon::now();
        
        // Buscar aniversariantes do dia
        $aniversariantes = Membro::whereDay('data_nascimento', $hoje->day)
            ->whereMonth('data_nascimento', $hoje->month)
            ->where('status', 'ativo')
            ->with(['assembleia', 'user'])
            ->get();
        
        Log::info("Job LembrarAniversariosJob executado. Encontrados " . $aniversariantes->count() . " aniversariantes.");
        
        foreach ($aniversariantes as $aniversariante) {
            try {
                // Notificar administradores da assembleia
                $adminsAssembleia = User::whereHas('membro', function ($query) use ($aniversariante) {
                    $query->where('assembleia_id', $aniversariante->assembleia_id);
                })
                // TODO: Adicionar verificação de permissão quando sistema estiver implementado
                // ->role('admin_assembleia')
                ->get();
                
                foreach ($adminsAssembleia as $admin) {
                    $admin->notify(new AniversarioNotification($aniversariante));
                }
                
                // Se o aniversariante tem usuário, notificar ele também
                if ($aniversariante->user) {
                    $aniversariante->user->notify(new AniversarioNotification($aniversariante, true));
                }
                
            } catch (\Exception $e) {
                Log::error("Erro ao notificar aniversário de {$aniversariante->nome_completo}: " . $e->getMessage());
            }
        }
    }
}
