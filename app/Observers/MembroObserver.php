<?php

namespace App\Observers;

use App\Models\Membro;
use App\Models\AniversarioCache;

class MembroObserver
{
    /**
     * Handle the Membro "creating" event.
     */
    public function creating(Membro $membro): void
    {
        // Auto-incrementar numero_membro se não foi definido
        if (empty($membro->numero_membro)) {
            $ultimoNumero = Membro::max('numero_membro') ?? 0;
            $membro->numero_membro = $ultimoNumero + 1;
        }
    }

    /**
     * Handle the Membro "created" event.
     */
    public function created(Membro $membro): void
    {
        // Atualizar cache de aniversários
        $this->atualizarCacheAniversarios($membro);
    }

    /**
     * Handle the Membro "updated" event.
     */
    public function updated(Membro $membro): void
    {
        // Se data de nascimento ou iniciação mudou, atualizar cache
        if ($membro->wasChanged(['data_nascimento', 'data_iniciacao'])) {
            $this->atualizarCacheAniversarios($membro);
        }

        // Verificar se atingiu maioridade (20 anos)
        if ($membro->status === 'ativa' && $membro->data_nascimento) {
            $idade = $membro->data_nascimento->diffInYears(now());
            if ($idade >= 20 && $membro->status !== 'maioridade') {
                $membro->update([
                    'status' => 'maioridade',
                    'data_maioridade' => now()
                ]);
            }
        }
    }

    /**
     * Handle the Membro "deleted" event.
     */
    public function deleted(Membro $membro): void
    {
        // Remover do cache de aniversários
        AniversarioCache::where('membro_id', $membro->id)->delete();
    }

    /**
     * Handle the Membro "restored" event.
     */
    public function restored(Membro $membro): void
    {
        // Recriar cache de aniversários
        $this->atualizarCacheAniversarios($membro);
    }

    /**
     * Handle the Membro "force deleted" event.
     */
    public function forceDeleted(Membro $membro): void
    {
        // Remover completamente do cache
        AniversarioCache::where('membro_id', $membro->id)->forceDelete();
    }

    /**
     * Atualizar cache de aniversários do membro
     */
    private function atualizarCacheAniversarios(Membro $membro): void
    {
        // Remover registros antigos
        AniversarioCache::where('membro_id', $membro->id)->delete();

        $registros = [];

        // Aniversário de nascimento
        if ($membro->data_nascimento) {
            $registros[] = [
                'membro_id' => $membro->id,
                'assembleia_id' => $membro->assembleia_id,
                'tipo' => 'membro',
                'mes' => $membro->data_nascimento->month,
                'dia' => $membro->data_nascimento->day,
                'updated_at' => now(),
            ];
        }

        // Aniversário de iniciação
        if ($membro->data_iniciacao) {
            $registros[] = [
                'membro_id' => $membro->id,
                'assembleia_id' => $membro->assembleia_id,
                'tipo' => 'iniciacao',
                'mes' => $membro->data_iniciacao->month,
                'dia' => $membro->data_iniciacao->day,
                'updated_at' => now(),
            ];
        }

        // Aniversário de maioridade
        if ($membro->data_maioridade) {
            $registros[] = [
                'membro_id' => $membro->id,
                'assembleia_id' => $membro->assembleia_id,
                'tipo' => 'maioridade',
                'mes' => $membro->data_maioridade->month,
                'dia' => $membro->data_maioridade->day,
                'updated_at' => now(),
            ];
        }

        if (!empty($registros)) {
            AniversarioCache::insert($registros);
        }
    }
}
