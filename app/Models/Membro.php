<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\HasCargos;
use App\Traits\HasAssembleia;

class Membro extends Model
{
    use SoftDeletes, HasUuid, HasCargos, HasAssembleia;

    protected $table = 'membros';

    protected $fillable = [
        'numero_membro',
        'user_id',
        'assembleia_id',
        'tipo_usuario_id',
        'nome_completo',
        'data_nascimento',
        'cpf',
        'telefone',
        'email',
        'endereco_completo',
        'nome_mae',
        'telefone_mae',
        'nome_pai',
        'telefone_pai',
        'responsavel_legal',
        'contato_responsavel',
        'data_iniciacao',
        'madrinha',
        'data_maioridade',
        'status',
        'motivo_afastamento',
        'foto',
        // Campos específicos para Tio Maçom
        'loja_maconica',
        'grau_maconico',
        'data_companheiro',
        'data_mestre',
        // Campos específicos para Tia Estrela do Oriente
        'capitulo_estrela',
        'data_iniciacao_arco_iris',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_iniciacao' => 'date',
        'data_maioridade' => 'date',
        'homenageados_ano' => 'date',
        'data_companheiro' => 'date',
        'data_mestre' => 'date',
        'data_iniciacao_arco_iris' => 'date',
    ];

    /**
     * Relacionamento: Membro pertence a um usuário (opcional)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento: Membro pertence a uma assembleia
     */
    public function assembleia(): BelongsTo
    {
        return $this->belongsTo(Assembleia::class);
    }

    /**
     * Relacionamento: Membro pertence a um tipo de usuário
     */
    public function tipoUsuario(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class);
    }

    /**
     * Relacionamento: Membro tem vários cargos no histórico
     */
    public function historicoCargos(): HasMany
    {
        return $this->hasMany(HistoricoCargo::class);
    }

    /**
     * Relacionamento: Membro pode estar em várias comissões
     */
    public function comissoes()
    {
        return $this->belongsToMany(Comissao::class, 'comissao_membros')
            ->withPivot('cargo', 'data_inicio', 'data_fim')
            ->withTimestamps();
    }

    /**
     * Relacionamento: Membro tem várias honrarias
     */
    public function honrarias(): HasMany
    {
        return $this->hasMany(HonrariaMembro::class);
    }

    /**
     * Relacionamento: Membro tem cargos de assembleia
     */
    public function cargosAssembleia(): HasMany
    {
        return $this->hasMany(CargoAssembleia::class);
    }

    /**
     * Relacionamento: Membro tem cargos de conselho
     */
    public function cargosConselho(): HasMany
    {
        return $this->hasMany(CargoConselho::class);
    }

    /**
     * Scope: Membros ativos
     */
    public function scopeAtivas($query)
    {
        return $query->where('status', 'ativa');
    }

    /**
     * Scope: Membros candidatas
     */
    public function scopeCandidatas($query)
    {
        return $query->where('status', 'candidata');
    }

    /**
     * Scope: Membros que atingiram maioridade
     */
    public function scopeMaioridade($query)
    {
        return $query->where('status', 'maioridade');
    }

    /**
     * Scope: Membros com Coração das Cores
     */
    public function scopeComCoracaoCores($query, $ano = null)
    {
        return $query->whereHas('honrarias', function ($q) use ($ano) {
            $q->where('tipo_honraria', 'coracao_cores');
            if ($ano) {
                $q->where('ano_recebimento', $ano);
            }
        });
    }

    /**
     * Scope: Membros com Grande Cruz das Cores
     */
    public function scopeComGrandeCruzCores($query, $ano = null)
    {
        return $query->whereHas('honrarias', function ($q) use ($ano) {
            $q->where('tipo_honraria', 'grande_cruz_cores');
            if ($ano) {
                $q->where('ano_recebimento', $ano);
            }
        });
    }

    /**
     * Scope: Membros Homenageados do Ano
     */
    public function scopeHomenageadosAno($query, $ano = null)
    {
        return $query->whereHas('honrarias', function ($q) use ($ano) {
            $q->where('tipo_honraria', 'homenageados_ano');
            if ($ano) {
                $q->where('ano_recebimento', $ano);
            }
        });
    }

    /**
     * Scope: Aniversariantes do mês
     */
    public function scopeAniversariantesDoMes($query, $mes = null)
    {
        $mes = $mes ?? now()->month;
        return $query->whereMonth('data_nascimento', $mes);
    }

    /**
     * Scope: Por assembleia
     */
    public function scopePorAssembleia($query, $assembleiaId)
    {
        return $query->where('assembleia_id', $assembleiaId);
    }

    /**
     * Scope: Por tipo de usuário
     */
    public function scopePorTipoUsuario($query, $tipoUsuarioId)
    {
        return $query->where('tipo_usuario_id', $tipoUsuarioId);
    }

    /**
     * Scope: Meninas Ativas
     */
    public function scopeMeninasAtivas($query)
    {
        return $query->whereHas('tipoUsuario', function ($q) {
            $q->where('codigo', TipoUsuario::MENINA_ATIVA);
        });
    }

    /**
     * Scope: Tios Maçons
     */
    public function scopeTiosMacons($query)
    {
        return $query->whereHas('tipoUsuario', function ($q) {
            $q->where('codigo', TipoUsuario::TIO_MACOM);
        });
    }

    /**
     * Scope: Tias Estrela do Oriente
     */
    public function scopeTiasEstrela($query)
    {
        return $query->whereHas('tipoUsuario', function ($q) {
            $q->where('codigo', TipoUsuario::TIA_ESTRELA);
        });
    }

    /**
     * Scope: Tios Maçons Mestres
     */
    public function scopeTiosMaconsMestres($query)
    {
        return $query->tiosMacons()->where('grau_maconico', 'mestre');
    }

    /**
     * Scope: Membros elegíveis para cargos de conselho
     */
    public function scopeElegiveisConselho($query)
    {
        return $query->whereHas('tipoUsuario', function ($q) {
            $q->whereIn('codigo', [
                TipoUsuario::TIO_MACOM,
                TipoUsuario::TIA_ESTRELA,
                TipoUsuario::MAIORIDADE,
                TipoUsuario::TIO,
                TipoUsuario::TIA
            ]);
        });
    }

    /**
     * Accessor: Idade calculada
     */
    public function getIdadeAttribute(): int
    {
        return $this->data_nascimento->age;
    }

    /**
     * Verifica se o membro é de um tipo específico
     */
    public function isTipoUsuario(string $codigo): bool
    {
        return $this->tipoUsuario?->codigo === $codigo;
    }

    /**
     * Verifica se o membro é Menina Ativa
     */
    public function isMeninaAtiva(): bool
    {
        return $this->isTipoUsuario(TipoUsuario::MENINA_ATIVA);
    }

    /**
     * Verifica se o membro é Maioridade
     */
    public function isMaioridade(): bool
    {
        return $this->isTipoUsuario(TipoUsuario::MAIORIDADE);
    }

    /**
     * Verifica se o membro é Tio Maçom
     */
    public function isTioMacom(): bool
    {
        return $this->isTipoUsuario(TipoUsuario::TIO_MACOM);
    }

    /**
     * Verifica se o membro é Tia Estrela do Oriente
     */
    public function isTiaEstrela(): bool
    {
        return $this->isTipoUsuario(TipoUsuario::TIA_ESTRELA);
    }

    /**
     * Verifica se o membro é Tio Maçom Mestre
     */
    public function isTioMacomMestre(): bool
    {
        return $this->isTioMacom() && $this->grau_maconico === 'mestre';
    }

    /**
     * Verifica se o membro é elegível para cargo de Presidente do Conselho
     */
    public function isElegivelPresidenteConselho(): bool
    {
        return $this->isTioMacomMestre();
    }

    /**
     * Verifica se o membro é elegível para cargos de conselho
     */
    public function isElegivelConselho(): bool
    {
        return in_array($this->tipoUsuario?->codigo, [
            TipoUsuario::TIO_MACOM,
            TipoUsuario::TIA_ESTRELA,
            TipoUsuario::MAIORIDADE,
            TipoUsuario::TIO,
            TipoUsuario::TIA
        ]);
    }

    /**
     * Verifica se o membro é elegível para honraria específica
     */
    public function isElegivelHonraria(string $tipoHonraria): bool
    {
        return !$this->honrarias()
            ->where('tipo_honraria', $tipoHonraria)
            ->exists();
    }

    /**
     * Obtém as regras de validação baseadas no tipo de usuário
     */
    public function getValidationRules(): array
    {
        if (!$this->tipoUsuario) {
            return [];
        }

        return $this->tipoUsuario->getValidationRules();
    }

    /**
     * Obtém as mensagens de validação baseadas no tipo de usuário
     */
    public function getValidationMessages(): array
    {
        if (!$this->tipoUsuario) {
            return [];
        }

        return $this->tipoUsuario->getValidationMessages();
    }

    /**
     * Valida os dados do membro baseado no seu tipo
     */
    public function validateByType(array $data): array
    {
        if (!$this->tipoUsuario) {
            return [];
        }

        $rules = $this->getValidationRules();
        $messages = $this->getValidationMessages();

        $validator = validator($data, $rules, $messages);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }

        return [];
    }

    /**
     * Verifica se o membro tem todos os campos obrigatórios preenchidos
     */
    public function hasRequiredFieldsComplete(): bool
    {
        if (!$this->tipoUsuario) {
            return false;
        }

        $errors = $this->validateByType($this->toArray());
        return empty($errors);
    }

    /**
     * Obtém o nome do tipo de usuário
     */
    public function getNomeTipoUsuario(): ?string
    {
        return $this->tipoUsuario?->nome;
    }

    /**
     * Obtém informações específicas do tipo formatadas
     */
    public function getInformacoesTipoFormatadas(): array
    {
        $info = [];

        if ($this->isTioMacom()) {
            $info['Loja Maçônica'] = $this->loja_maconica;
            $info['Grau Maçônico'] = ucfirst($this->grau_maconico ?? 'aprendiz');
            
            if ($this->data_companheiro) {
                $info['Data Companheiro'] = $this->data_companheiro->format('d/m/Y');
            }
            
            if ($this->data_mestre) {
                $info['Data Mestre'] = $this->data_mestre->format('d/m/Y');
            }
        }

        if ($this->isTiaEstrela()) {
            $info['Capítulo Estrela'] = $this->capitulo_estrela;
            
            if ($this->data_iniciacao_arco_iris) {
                $info['Data Iniciação Arco-Íris'] = $this->data_iniciacao_arco_iris->format('d/m/Y');
            }
        }

        return $info;
    }
}
