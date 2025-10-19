<?php

namespace App\Traits;

use App\Models\Assembleia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasAssembleia
{
    /**
     * Relacionamento: Pertence a uma assembleia
     */
    public function assembleia(): BelongsTo
    {
        return $this->belongsTo(Assembleia::class);
    }

    /**
     * Obter nome da assembleia
     */
    public function getNomeAssembleiaAttribute(): string
    {
        return $this->assembleia ? $this->assembleia->nome : 'Sem assembleia';
    }

    /**
     * Obter número da assembleia
     */
    public function getNumeroAssembleiaAttribute(): int
    {
        return $this->assembleia ? $this->assembleia->numero : 0;
    }

    /**
     * Obter cidade da assembleia
     */
    public function getCidadeAssembleiaAttribute(): string
    {
        return $this->assembleia ? $this->assembleia->cidade : 'Sem cidade';
    }

    /**
     * Verificar se a assembleia está ativa
     */
    public function assembleiaAtiva(): bool
    {
        return $this->assembleia && $this->assembleia->ativa;
    }

    /**
     * Verificar se pertence à assembleia específica
     */
    public function pertenceAssembleia(int $assembleiaId): bool
    {
        return $this->assembleia_id === $assembleiaId;
    }

    /**
     * Verificar se pode administrar a assembleia
     */
    public function podeAdministrarAssembleia(): bool
    {
        // Membros da jurisdição podem administrar qualquer assembleia
        if (method_exists($this, 'hasRole') && $this->hasRole('membro_jurisdicao')) {
            return true;
        }

        // Admins de assembleia podem administrar apenas sua assembleia
        if (method_exists($this, 'hasRole') && $this->hasRole('admin_assembleia')) {
            return true;
        }

        return false;
    }

    /**
     * Obter membros da mesma assembleia
     */
    public function membrosDaMesmaAssembleia()
    {
        if (!$this->assembleia_id) {
            return collect();
        }

        return $this->assembleia->membros()->where('id', '!=', $this->id);
    }

    /**
     * Obter estatísticas da assembleia
     */
    public function getEstatisticasAssembleia(): array
    {
        if (!$this->assembleia) {
            return [];
        }

        $assembleia = $this->assembleia;
        
        return [
            'total_membros' => $assembleia->membros()->count(),
            'membros_ativas' => $assembleia->membros()->where('status', 'ativa')->count(),
            'membros_candidatas' => $assembleia->membros()->where('status', 'candidata')->count(),
            'membros_maioridade' => $assembleia->membros()->where('status', 'maioridade')->count(),
            'cargos_preenchidos' => $assembleia->cargos()->whereNotNull('membro_id')->where('ativo', true)->count(),
            'cargos_vagos' => $assembleia->cargos()->whereNull('membro_id')->where('ativo', true)->count(),
        ];
    }

    /**
     * Scope: Filtrar por assembleia
     */
    public function scopePorAssembleia($query, int $assembleiaId)
    {
        return $query->where('assembleia_id', $assembleiaId);
    }

    /**
     * Scope: Apenas membros de assembleias ativas
     */
    public function scopeAssembleiasAtivas($query)
    {
        return $query->whereHas('assembleia', function ($query) {
            $query->where('ativa', true);
        });
    }
}