<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class HonrariaMembro extends Model
{
    use HasFactory;

    protected $table = 'honrarias_membros';

    protected $fillable = [
        'membro_id',
        'tipo_honraria',
        'ano_recebimento',
        'observacoes',
        'atribuido_por',
    ];

    protected $casts = [
        'ano_recebimento' => 'integer',
    ];

    /**
     * Tipos de honrarias disponíveis
     */
    public const TIPOS = [
        'coracao_cores' => 'Coração das Cores',
        'grande_cruz_cores' => 'Grande Cruz das Cores',
        'homenageados_ano' => 'Homenageados do Ano',
    ];

    /**
     * Relacionamento com o membro
     */
    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class);
    }

    /**
     * Relacionamento com o usuário que atribuiu a honraria
     */
    public function atribuidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atribuido_por');
    }

    /**
     * Accessor para o nome da honraria
     */
    public function getNomeTipoHonrariaAttribute(): string
    {
        return self::TIPOS[$this->tipo_honraria] ?? $this->tipo_honraria;
    }

    /**
     * Scope para filtrar por tipo de honraria
     */
    public function scopeDoTipo($query, string $tipo)
    {
        return $query->where('tipo_honraria', $tipo);
    }

    /**
     * Scope para filtrar por ano
     */
    public function scopeDoAno($query, int $ano)
    {
        return $query->where('ano_recebimento', $ano);
    }

    /**
     * Scope para filtrar por Coração das Cores
     */
    public function scopeCoracaoCores($query)
    {
        return $query->doTipo('coracao_cores');
    }

    /**
     * Scope para filtrar por Grande Cruz das Cores
     */
    public function scopeGrandeCruzCores($query)
    {
        return $query->doTipo('grande_cruz_cores');
    }

    /**
     * Scope para filtrar por Homenageados do Ano
     */
    public function scopeHomenageadosAno($query)
    {
        return $query->doTipo('homenageados_ano');
    }

    /**
     * Boot do modelo para validações
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($honraria) {
            $honraria->validarRegrasHonrarias();
        });
    }

    /**
     * Valida as regras específicas de cada tipo de honraria
     */
    private function validarRegrasHonrarias()
    {
        // Para Coração das Cores e Grande Cruz das Cores: apenas uma vez na vida
        if (in_array($this->tipo_honraria, ['coracao_cores', 'grande_cruz_cores'])) {
            $existente = static::where('membro_id', $this->membro_id)
                ->where('tipo_honraria', $this->tipo_honraria)
                ->where('id', '!=', $this->id ?? 0)
                ->first();

            if ($existente) {
                $nomeHonraria = self::TIPOS[$this->tipo_honraria];
                $anoExistente = $existente->ano_recebimento;
                throw ValidationException::withMessages([
                    'tipo_honraria' => "O membro já recebeu a honraria '{$nomeHonraria}' em {$anoExistente}. Esta honraria só pode ser recebida uma vez na vida."
                ]);
            }
        }

        // Para Homenageados do Ano: apenas uma vez por ano
        if ($this->tipo_honraria === 'homenageados_ano') {
            $existente = static::where('membro_id', $this->membro_id)
                ->where('tipo_honraria', 'homenageados_ano')
                ->where('ano_recebimento', $this->ano_recebimento)
                ->where('id', '!=', $this->id ?? 0)
                ->exists();

            if ($existente) {
                throw ValidationException::withMessages([
                    'ano_recebimento' => "O membro já foi homenageado no ano {$this->ano_recebimento}. Apenas uma homenagem por ano é permitida."
                ]);
            }
        }
    }
}