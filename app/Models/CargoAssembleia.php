<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasUuid;

class CargoAssembleia extends Model
{
    use SoftDeletes, HasUuid;

    protected $table = 'cargos_assembleia';

    protected $fillable = [
        'assembleia_id',
        'membro_id',
        'tipo_cargo_id',
        'data_inicio',
        'data_fim',
        'ativo',
        'protocolo_id',
        'atribuido_por',
        'observacoes',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento: Cargo pertence a uma assembleia
     */
    public function assembleia(): BelongsTo
    {
        return $this->belongsTo(Assembleia::class);
    }

    /**
     * Relacionamento: Cargo pertence a um membro
     */
    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class);
    }

    /**
     * Relacionamento: Cargo é de um tipo específico
     */
    public function tipoCargo(): BelongsTo
    {
        return $this->belongsTo(TipoCargoAssembleia::class, 'tipo_cargo_id');
    }

    /**
     * Relacionamento: Cargo foi atribuído através de um protocolo
     */
    public function protocolo(): BelongsTo
    {
        return $this->belongsTo(Protocolo::class);
    }

    /**
     * Relacionamento: Cargo foi atribuído por um usuário
     */
    public function atribuidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atribuido_por');
    }

    /**
     * Relacionamento: Histórico deste cargo
     */
    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoCargo::class);
    }

    /**
     * Scope: Cargos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope: Cargos vigentes (sem data fim ou data fim futura)
     */
    public function scopeVigentes($query)
    {
        return $query->where('ativo', true)
            ->where(function ($q) {
                $q->whereNull('data_fim')
                  ->orWhere('data_fim', '>=', now());
            });
    }

    /**
     * Scope: Por assembleia
     */
    public function scopePorAssembleia($query, $assembleiaId)
    {
        return $query->where('assembleia_id', $assembleiaId);
    }

    /**
     * Scope: Por tipo de cargo
     */
    public function scopePorTipo($query, $tipoCargoId)
    {
        return $query->where('tipo_cargo_id', $tipoCargoId);
    }

    /**
     * Scope: Por protocolo
     */
    public function scopePorProtocolo(Builder $query, $protocoloId): Builder
    {
        return $query->where('protocolo_id', $protocoloId);
    }

    /**
     * Scope: Apenas meninas ativas
     */
    public function scopeMeninasAtivas(Builder $query): Builder
    {
        return $query->whereHas('membro', function ($q) {
            $q->meninasAtivas()->ativos();
        });
    }

    /**
     * Verifica se o membro é elegível para cargo de assembleia
     */
    public function validarElegibilidadeMembro(): array
    {
        $errors = [];

        if (!$this->membro) {
            $errors[] = 'Membro não encontrado';
            return $errors;
        }

        // Apenas meninas ativas podem ter cargos de assembleia
        if (!$this->membro->isMeninaAtiva()) {
            $errors[] = 'Apenas Meninas Ativas podem ocupar cargos de assembleia';
        }

        // Verifica se o membro está ativo
        if ($this->membro->status !== 'ativa') {
            $errors[] = 'Membro deve estar com status ativo';
        }

        // Validação de unicidade do cargo na assembleia
        if ($this->tipo_cargo_id) {
            $cargoExistente = static::where('assembleia_id', $this->assembleia_id)
                ->where('tipo_cargo_id', $this->tipo_cargo_id)
                ->where('ativo', true)
                ->where('id', '!=', $this->id)
                ->first();

            if ($cargoExistente) {
                $tipoCargoNome = $this->tipoCargo?->nome ?? 'este cargo';
                $errors[] = "Já existe um membro ocupando {$tipoCargoNome} nesta assembleia";
            }
        }

        return $errors;
    }

    /**
     * Finaliza o cargo (define data_fim e desativa)
     */
    public function finalizar(?string $observacao = null): void
    {
        $this->update([
            'data_fim' => now(),
            'ativo' => false,
            'observacoes' => $observacao ? 
                ($this->observacoes ? $this->observacoes . "\n" . $observacao : $observacao) : 
                $this->observacoes
        ]);
    }

    /**
     * Ativa o cargo
     */
    public function ativar(): void
    {
        $this->update([
            'ativo' => true,
            'data_fim' => null
        ]);
    }

    /**
     * Obtém o histórico de posições do membro
     */
    public function getHistoricoMembro(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('membro_id', $this->membro_id)
            ->where('assembleia_id', $this->assembleia_id)
            ->with(['tipoCargo', 'protocolo', 'atribuidoPor'])
            ->orderBy('data_inicio', 'desc')
            ->get();
    }

    /**
     * Verifica se existe conflito de cargo na assembleia
     */
    public static function verificarConflitoCargo(
        int $assembleiaId, 
        int $tipoCargoId, 
        ?string $cargoId = null
    ): bool {
        return static::where('assembleia_id', $assembleiaId)
            ->where('tipo_cargo_id', $tipoCargoId)
            ->where('ativo', true)
            ->when($cargoId, fn($q) => $q->where('id', '!=', $cargoId))
            ->exists();
    }

    /**
     * Obtém o cargo ativo de um tipo específico em uma assembleia
     */
    public static function getCargoAtivo(int $assembleiaId, int $tipoCargoId): ?self
    {
        return static::where('assembleia_id', $assembleiaId)
            ->where('tipo_cargo_id', $tipoCargoId)
            ->where('ativo', true)
            ->first();
    }

    /**
     * Atualiza todos os cargos de uma assembleia simultaneamente
     */
    public static function atualizarCargosAssembleia(
        int $assembleiaId, 
        array $novosCargos, 
        ?string $protocoloId = null,
        ?string $atribuidoPor = null
    ): array {
        $resultados = [];

        // Desativa todos os cargos atuais
        static::where('assembleia_id', $assembleiaId)
            ->where('ativo', true)
            ->update([
                'ativo' => false,
                'data_fim' => now(),
                'observacoes' => 'Finalizado por atualização de cargos'
            ]);

        // Cria os novos cargos
        foreach ($novosCargos as $tipoCargoId => $membroId) {
            if ($membroId) {
                $cargo = static::create([
                    'assembleia_id' => $assembleiaId,
                    'membro_id' => $membroId,
                    'tipo_cargo_id' => $tipoCargoId,
                    'data_inicio' => now(),
                    'ativo' => true,
                    'protocolo_id' => $protocoloId,
                    'atribuido_por' => $atribuidoPor,
                ]);

                $resultados[] = $cargo;
            }
        }

        return $resultados;
    }

    /**
     * Boot method para eventos do modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Validação antes de salvar
        static::saving(function ($cargo) {
            $errors = $cargo->validarElegibilidadeMembro();
            if (!empty($errors)) {
                throw new \InvalidArgumentException(implode(', ', $errors));
            }
        });
    }
}
