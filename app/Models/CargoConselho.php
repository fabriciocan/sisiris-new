<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasUuid;
use Spatie\Permission\Models\Role;

class CargoConselho extends Model
{
    use SoftDeletes, HasUuid;

    protected $table = 'cargo_conselhos';

    protected $fillable = [
        'assembleia_id',
        'membro_id',
        'tipo_cargo',
        'data_inicio',
        'data_fim',
        'ativo',
        'concede_admin_acesso',
        'protocolo_id',
        'atribuido_por',
        'observacoes',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
        'concede_admin_acesso' => 'boolean',
    ];

    // Constantes para tipos de cargo
    public const PRESIDENTE = 'presidente';
    public const PRECEPTORA_MAE = 'preceptora_mae';
    public const PRECEPTORA_MAE_ADJUNTA = 'preceptora_mae_adjunta';
    public const MEMBRO_CONSELHO = 'membro_conselho';

    // Cargos que concedem acesso de admin
    public const CARGOS_ADMIN = [
        self::PRESIDENTE,
        self::PRECEPTORA_MAE,
        self::PRECEPTORA_MAE_ADJUNTA,
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
     * Scope: Cargos ativos
     */
    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope: Cargos vigentes (sem data fim ou data fim futura)
     */
    public function scopeVigentes(Builder $query): Builder
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
    public function scopePorAssembleia(Builder $query, $assembleiaId): Builder
    {
        return $query->where('assembleia_id', $assembleiaId);
    }

    /**
     * Scope: Por tipo de cargo
     */
    public function scopePorTipo(Builder $query, string $tipoCargo): Builder
    {
        return $query->where('tipo_cargo', $tipoCargo);
    }

    /**
     * Scope: Cargos que concedem acesso de admin
     */
    public function scopeComAcessoAdmin(Builder $query): Builder
    {
        return $query->where('concede_admin_acesso', true);
    }

    /**
     * Scope: Presidentes do conselho
     */
    public function scopePresidentes(Builder $query): Builder
    {
        return $query->where('tipo_cargo', self::PRESIDENTE);
    }

    /**
     * Scope: Preceptoras Mãe
     */
    public function scopePreceptorasMae(Builder $query): Builder
    {
        return $query->whereIn('tipo_cargo', [
            self::PRECEPTORA_MAE,
            self::PRECEPTORA_MAE_ADJUNTA
        ]);
    }

    /**
     * Verifica se o cargo concede acesso de admin
     */
    public function concedeAcessoAdmin(): bool
    {
        return $this->concede_admin_acesso || in_array($this->tipo_cargo, self::CARGOS_ADMIN);
    }

    /**
     * Verifica se é um cargo executivo
     */
    public function isCargoExecutivo(): bool
    {
        return in_array($this->tipo_cargo, self::CARGOS_ADMIN);
    }

    /**
     * Verifica se é presidente do conselho
     */
    public function isPresidente(): bool
    {
        return $this->tipo_cargo === self::PRESIDENTE;
    }

    /**
     * Verifica se é preceptora mãe (titular ou adjunta)
     */
    public function isPreceptoraMae(): bool
    {
        return in_array($this->tipo_cargo, [
            self::PRECEPTORA_MAE,
            self::PRECEPTORA_MAE_ADJUNTA
        ]);
    }

    /**
     * Obtém o nome formatado do cargo
     */
    public function getNomeCargoFormatado(): string
    {
        return match($this->tipo_cargo) {
            self::PRESIDENTE => 'Presidente do Conselho Consultivo',
            self::PRECEPTORA_MAE => 'Preceptora Mãe',
            self::PRECEPTORA_MAE_ADJUNTA => 'Preceptora Mãe Adjunta',
            self::MEMBRO_CONSELHO => 'Membro do Conselho',
            default => ucfirst(str_replace('_', ' ', $this->tipo_cargo))
        };
    }

    /**
     * Valida se o membro é elegível para o cargo
     */
    public function validarElegibilidadeMembro(): array
    {
        $errors = [];

        if (!$this->membro) {
            $errors[] = 'Membro não encontrado';
            return $errors;
        }

        // Validação específica para Presidente do Conselho
        if ($this->tipo_cargo === self::PRESIDENTE) {
            if (!$this->membro->isTioMacomMestre()) {
                $errors[] = 'Presidente do Conselho deve ser Tio Maçom com grau Mestre';
            }
        }

        // Validação geral para cargos de conselho
        if (!$this->membro->isElegivelConselho()) {
            $errors[] = 'Membro não é elegível para cargos de conselho';
        }

        // Validação de unicidade do cargo na assembleia
        $cargoExistente = static::where('assembleia_id', $this->assembleia_id)
            ->where('tipo_cargo', $this->tipo_cargo)
            ->where('ativo', true)
            ->where('id', '!=', $this->id)
            ->first();

        if ($cargoExistente) {
            $errors[] = "Já existe um {$this->getNomeCargoFormatado()} ativo nesta assembleia";
        }

        return $errors;
    }

    /**
     * Atualiza as permissões do usuário baseado no cargo
     */
    public function atualizarPermissoesUsuario(): void
    {
        if (!$this->membro->user) {
            return;
        }

        $user = $this->membro->user;

        // Se o cargo concede acesso de admin, atualiza o nível de acesso
        if ($this->concedeAcessoAdmin() && $this->ativo) {
            $user->update(['nivel_acesso' => 'admin_assembleia']);
            
            // Atribui role de admin assembleia se não tiver
            if (!$user->hasRole('admin_assembleia')) {
                $user->assignRole('admin_assembleia');
            }
        }

        // Permissões específicas por cargo
        $this->atribuirPermissoesPorCargo($user);
    }

    /**
     * Atribui permissões específicas baseadas no tipo de cargo
     */
    private function atribuirPermissoesPorCargo(User $user): void
    {
        switch ($this->tipo_cargo) {
            case self::PRESIDENTE:
                $user->givePermissionTo([
                    'protocolos.approve',
                    'cargos.assign',
                    'membros.manage',
                    'assembleia.manage'
                ]);
                break;

            case self::PRECEPTORA_MAE:
            case self::PRECEPTORA_MAE_ADJUNTA:
                $user->givePermissionTo([
                    'protocolos.create',
                    'protocolos.approve',
                    'membros.manage'
                ]);
                break;

            case self::MEMBRO_CONSELHO:
                $user->givePermissionTo([
                    'protocolos.view',
                    'membros.view'
                ]);
                break;
        }
    }

    /**
     * Remove as permissões quando o cargo é desativado
     */
    public function removerPermissoesUsuario(): void
    {
        if (!$this->membro->user) {
            return;
        }

        $user = $this->membro->user;

        // Verifica se o usuário tem outros cargos ativos que concedem admin
        $outroCargoAdmin = static::where('membro_id', $this->membro_id)
            ->where('id', '!=', $this->id)
            ->where('ativo', true)
            ->where('concede_admin_acesso', true)
            ->exists();

        // Se não tem outros cargos admin, remove o acesso
        if (!$outroCargoAdmin) {
            $user->update(['nivel_acesso' => 'membro']);
            $user->removeRole('admin_assembleia');
        }

        // Remove permissões específicas do cargo
        $this->removerPermissoesPorCargo($user);
    }

    /**
     * Remove permissões específicas do cargo
     */
    private function removerPermissoesPorCargo(User $user): void
    {
        // Verifica se o usuário tem outros cargos que concedem as mesmas permissões
        $outrosCargos = static::where('membro_id', $this->membro_id)
            ->where('id', '!=', $this->id)
            ->where('ativo', true)
            ->get();

        $permissoesParaManter = [];
        foreach ($outrosCargos as $cargo) {
            $permissoesParaManter = array_merge(
                $permissoesParaManter,
                $this->getPermissoesPorTipoCargo($cargo->tipo_cargo)
            );
        }

        $permissoesAtual = $this->getPermissoesPorTipoCargo($this->tipo_cargo);
        $permissoesParaRemover = array_diff($permissoesAtual, $permissoesParaManter);

        if (!empty($permissoesParaRemover)) {
            $user->revokePermissionTo($permissoesParaRemover);
        }
    }

    /**
     * Obtém as permissões por tipo de cargo
     */
    private function getPermissoesPorTipoCargo(string $tipoCargo): array
    {
        return match($tipoCargo) {
            self::PRESIDENTE => [
                'protocolos.approve',
                'cargos.assign',
                'membros.manage',
                'assembleia.manage'
            ],
            self::PRECEPTORA_MAE, self::PRECEPTORA_MAE_ADJUNTA => [
                'protocolos.create',
                'protocolos.approve',
                'membros.manage'
            ],
            self::MEMBRO_CONSELHO => [
                'protocolos.view',
                'membros.view'
            ],
            default => []
        };
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

        $this->removerPermissoesUsuario();
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

        $this->atualizarPermissoesUsuario();
    }

    /**
     * Obtém todos os tipos de cargo disponíveis
     */
    public static function getTiposCargo(): array
    {
        return [
            self::PRESIDENTE => 'Presidente do Conselho Consultivo',
            self::PRECEPTORA_MAE => 'Preceptora Mãe',
            self::PRECEPTORA_MAE_ADJUNTA => 'Preceptora Mãe Adjunta',
            self::MEMBRO_CONSELHO => 'Membro do Conselho',
        ];
    }

    /**
     * Verifica se existe conflito de cargo na assembleia
     */
    public static function verificarConflitoCargo(
        int $assembleiaId, 
        string $tipoCargo, 
        ?string $cargoId = null
    ): bool {
        return static::where('assembleia_id', $assembleiaId)
            ->where('tipo_cargo', $tipoCargo)
            ->where('ativo', true)
            ->when($cargoId, fn($q) => $q->where('id', '!=', $cargoId))
            ->exists();
    }

    /**
     * Obtém o cargo ativo de um tipo específico em uma assembleia
     */
    public static function getCargoAtivo(int $assembleiaId, string $tipoCargo): ?self
    {
        return static::where('assembleia_id', $assembleiaId)
            ->where('tipo_cargo', $tipoCargo)
            ->where('ativo', true)
            ->first();
    }

    /**
     * Boot method para eventos do modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Ao criar um cargo, define automaticamente se concede admin
        static::creating(function ($cargo) {
            if (in_array($cargo->tipo_cargo, self::CARGOS_ADMIN)) {
                $cargo->concede_admin_acesso = true;
            }
        });

        // Ao salvar, atualiza permissões
        static::saved(function ($cargo) {
            if ($cargo->ativo) {
                $cargo->atualizarPermissoesUsuario();
            }
        });

        // Ao deletar, remove permissões
        static::deleting(function ($cargo) {
            $cargo->removerPermissoesUsuario();
        });
    }
}