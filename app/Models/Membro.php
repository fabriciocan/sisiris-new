<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class Membro extends Model
{
    use SoftDeletes, HasUuid;

    protected $table = 'membros';

    protected $fillable = [
        'user_id',
        'assembleia_id',
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
        'membro_cruz',
        'coracao_cores',
        'homenageados_ano',
        'foto',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_iniciacao' => 'date',
        'data_maioridade' => 'date',
        'homenageados_ano' => 'date',
        'membro_cruz' => 'boolean',
        'coracao_cores' => 'boolean',
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
     * Scope: Membros da cruz
     */
    public function scopeMembrosCruz($query)
    {
        return $query->where('membro_cruz', true);
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
     * Accessor: Idade calculada
     */
    public function getIdadeAttribute(): int
    {
        return $this->data_nascimento->age;
    }
}
