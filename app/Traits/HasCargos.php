<?php

namespace App\Traits;

use App\Models\CargoAssembleia;
use App\Models\CargoGrandeAssembleia;
use App\Models\HistoricoCargo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasCargos
{
    /**
     * Relacionamento: Cargos de assembleia do membro
     */
    public function cargosAssembleia(): HasMany
    {
        return $this->hasMany(CargoAssembleia::class, 'membro_id');
    }

    /**
     * Relacionamento: Cargos de grande assembleia do membro
     */
    public function cargosGrandeAssembleia(): HasMany
    {
        return $this->hasMany(CargoGrandeAssembleia::class, 'membro_id');
    }

    /**
     * Relacionamento: Histórico de cargos do membro
     */
    public function historicoCargos(): HasMany
    {
        return $this->hasMany(HistoricoCargo::class, 'membro_id');
    }

    /**
     * Obter cargos ativos de assembleia
     */
    public function cargosAssembleiaAtivos()
    {
        return $this->cargosAssembleia()->where('ativo', true);
    }

    /**
     * Obter cargos ativos de grande assembleia
     */
    public function cargosGrandeAssembleiaAtivos()
    {
        return $this->cargosGrandeAssembleia()->where('ativo', true);
    }

    /**
     * Verificar se tem cargo administrativo ativo
     */
    public function temCargoAdministrativo(): bool
    {
        return $this->cargosAssembleiaAtivos()
            ->whereHas('tipoCargo', function ($query) {
                $query->where('is_admin', true);
            })
            ->exists();
    }

    /**
     * Verificar se tem cargo específico ativo
     */
    public function temCargo(string $nomeCargo): bool
    {
        return $this->cargosAssembleiaAtivos()
            ->whereHas('tipoCargo', function ($query) use ($nomeCargo) {
                $query->where('nome', $nomeCargo);
            })
            ->exists() ||
            $this->cargosGrandeAssembleiaAtivos()
            ->whereHas('tipoCargo', function ($query) use ($nomeCargo) {
                $query->where('nome', $nomeCargo);
            })
            ->exists();
    }

    /**
     * Obter todos os cargos ativos (assembleia + grande assembleia)
     */
    public function todosOsCargosAtivos()
    {
        $cargosAssembleia = $this->cargosAssembleiaAtivos()->with('tipoCargo')->get();
        $cargosGrandeAssembleia = $this->cargosGrandeAssembleiaAtivos()->with('tipoCargo')->get();
        
        return $cargosAssembleia->merge($cargosGrandeAssembleia);
    }

    /**
     * Finalizar cargo de assembleia
     */
    public function finalizarCargoAssembleia(int $cargoId, string $motivo = null): bool
    {
        $cargo = $this->cargosAssembleia()->find($cargoId);
        
        if ($cargo && $cargo->ativo) {
            $cargo->update([
                'ativo' => false,
                'data_fim' => now(),
                'observacoes' => $motivo
            ]);

            // Registrar no histórico
            HistoricoCargo::create([
                'membro_id' => $this->id,
                'tipo_cargo_id' => $cargo->tipo_cargo_id,
                'cargo_assembleia_id' => $cargo->id,
                'assembleia_id' => $cargo->assembleia_id,
                'semestre' => now()->format('Y') . '.' . (now()->month <= 6 ? '1' : '2'),
                'tipo_historico' => 'assembleia'
            ]);

            return true;
        }

        return false;
    }
}