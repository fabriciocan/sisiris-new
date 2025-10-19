<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoUsuario extends Model
{
    use HasFactory;

    protected $table = 'tipo_usuarios';

    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'requer_assembleia',
        'requer_pais_responsaveis',
        'campos_especificos',
    ];

    protected $casts = [
        'requer_assembleia' => 'boolean',
        'requer_pais_responsaveis' => 'boolean',
        'campos_especificos' => 'array',
    ];

    /**
     * Relacionamento: TipoUsuario tem muitos membros
     */
    public function membros(): HasMany
    {
        return $this->hasMany(Membro::class);
    }

    /**
     * Relacionamento: TipoUsuario tem muitos usuários
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope: Tipos que requerem assembleia
     */
    public function scopeRequerAssembleia($query)
    {
        return $query->where('requer_assembleia', true);
    }

    /**
     * Scope: Tipos que requerem pais/responsáveis
     */
    public function scopeRequerPaisResponsaveis($query)
    {
        return $query->where('requer_pais_responsaveis', true);
    }

    /**
     * Verifica se este tipo de usuário requer informações de assembleia
     */
    public function requerInformacoesAssembleia(): bool
    {
        return $this->requer_assembleia;
    }

    /**
     * Verifica se este tipo de usuário requer informações de pais/responsáveis
     */
    public function requerInformacoesPaisResponsaveis(): bool
    {
        return $this->requer_pais_responsaveis;
    }

    /**
     * Obtém os campos específicos para este tipo de usuário
     */
    public function getCamposEspecificos(): array
    {
        return $this->campos_especificos ?? [];
    }

    /**
     * Verifica se um campo específico é obrigatório para este tipo
     */
    public function campoEObrigatorio(string $campo): bool
    {
        $campos = $this->getCamposEspecificos();
        return isset($campos[$campo]) && ($campos[$campo]['obrigatorio'] ?? false);
    }

    /**
     * Obtém as regras de validação para este tipo de usuário
     */
    public function getValidationRules(): array
    {
        $rules = [];

        // Regras básicas sempre aplicadas
        $rules['nome_completo'] = 'required|string|max:255';
        $rules['data_nascimento'] = 'required|date|before:today';
        $rules['cpf'] = 'required|string|size:11|unique:membros,cpf';
        $rules['telefone'] = 'required|string|max:20';
        $rules['email'] = 'required|email|unique:membros,email';
        $rules['endereco_completo'] = 'required|string|max:500';

        // Regras específicas baseadas no tipo
        if ($this->requer_assembleia) {
            $rules['assembleia_id'] = 'required|exists:assembleias,id';
            $rules['data_iniciacao'] = 'required|date|before_or_equal:today';
        }

        if ($this->requer_pais_responsaveis) {
            $rules['nome_mae'] = 'required|string|max:255';
            $rules['telefone_mae'] = 'required|string|max:20';
            $rules['nome_pai'] = 'nullable|string|max:255';
            $rules['telefone_pai'] = 'nullable|string|max:20';
            $rules['responsavel_legal'] = 'nullable|string|max:255';
            $rules['contato_responsavel'] = 'nullable|string|max:20';
        }

        // Regras específicas por tipo
        switch ($this->codigo) {
            case 'tio_macom':
                $rules['loja_maconica'] = 'required|string|max:200';
                $rules['grau_maconico'] = 'required|in:aprendiz,companheiro,mestre';
                $rules['data_companheiro'] = 'nullable|date|before_or_equal:today|after:data_iniciacao';
                $rules['data_mestre'] = 'nullable|date|before_or_equal:today|after:data_companheiro';
                break;

            case 'tia_estrela':
                $rules['capitulo_estrela'] = 'required|string|max:200';
                $rules['data_iniciacao_arco_iris'] = 'nullable|date|before_or_equal:today';
                break;
        }

        return $rules;
    }

    /**
     * Obtém as mensagens de validação personalizadas
     */
    public function getValidationMessages(): array
    {
        return [
            'assembleia_id.required' => 'A assembleia é obrigatória para este tipo de usuário.',
            'data_iniciacao.required' => 'A data de iniciação é obrigatória.',
            'nome_mae.required' => 'O nome da mãe é obrigatório para menores de idade.',
            'telefone_mae.required' => 'O telefone da mãe é obrigatório para menores de idade.',
            'loja_maconica.required' => 'A loja maçônica é obrigatória para Tios Maçons.',
            'grau_maconico.required' => 'O grau maçônico é obrigatório para Tios Maçons.',
            'capitulo_estrela.required' => 'O capítulo da Estrela do Oriente é obrigatório.',
            'data_companheiro.after' => 'A data de companheiro deve ser posterior à data de iniciação.',
            'data_mestre.after' => 'A data de mestre deve ser posterior à data de companheiro.',
        ];
    }

    /**
     * Constantes para os códigos de tipos de usuário
     */
    public const MENINA_ATIVA = 'menina_ativa';
    public const MAIORIDADE = 'maioridade';
    public const TIO_MACOM = 'tio_macom';
    public const TIA_ESTRELA = 'tia_estrela';
    public const TIO = 'tio';
    public const TIA = 'tia';

    /**
     * Obtém todos os tipos de usuário disponíveis
     */
    public static function getTiposDisponiveis(): array
    {
        return [
            self::MENINA_ATIVA => 'Menina Ativa',
            self::MAIORIDADE => 'Maioridade',
            self::TIO_MACOM => 'Tio Maçom',
            self::TIA_ESTRELA => 'Tia Estrela do Oriente',
            self::TIO => 'Tio',
            self::TIA => 'Tia',
        ];
    }
}