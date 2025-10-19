<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasUuid;
use App\Traits\HasProtocolos;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuid, HasProtocolos;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telefone',
        'data_nascimento',
        'cpf',
        'tipo_usuario_id',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'data_nascimento' => 'date',
            'password_changed_at' => 'datetime',
        ];
    }

    /**
     * Verifica se o usuário pode acessar o painel Filament
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Verifica se o usuário tem um perfil de membro
        if ($this->membro) {
            // Se for membro, só permite acesso se estiver ativa
            // Status permitidos: 'candidata', 'ativa', 'maioridade'
            // Status bloqueados: 'afastada', 'desligada'
            if (in_array($this->membro->status, ['afastada', 'desligada'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Relacionamento: Usuário pertence a um tipo de usuário
     */
    public function tipoUsuario(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class);
    }

    /**
     * Relacionamento: Usuário pode ter um perfil de membro
     */
    public function membro(): HasOne
    {
        return $this->hasOne(Membro::class);
    }

    /**
     * Relacionamento: Usuário pode estar em várias comissões
     */
    public function comissoes(): BelongsToMany
    {
        return $this->belongsToMany(Comissao::class, 'comissao_membros')
            ->withPivot('cargo', 'data_inicio', 'data_fim', 'ativo')
            ->withTimestamps();
    }

    /**
     * Relacionamento: Protocolos solicitados pelo usuário
     */
    public function protocolosSolicitados(): HasMany
    {
        return $this->hasMany(Protocolo::class, 'solicitante_id');
    }

    /**
     * Relacionamento: Tickets abertos pelo usuário
     */
    public function ticketsSolicitados(): HasMany
    {
        return $this->hasMany(Ticket::class, 'solicitante_id');
    }

    /**
     * Relacionamento: Tickets sob responsabilidade do usuário
     */
    public function ticketsResponsavel(): HasMany
    {
        return $this->hasMany(Ticket::class, 'responsavel_id');
    }

    /**
     * Relacionamento: Respostas de tickets do usuário
     */
    public function ticketRespostas(): HasMany
    {
        return $this->hasMany(TicketResposta::class);
    }

    /**
     * Relacionamento: Eventos criados pelo usuário
     */
    public function eventosCriados(): HasMany
    {
        return $this->hasMany(EventoCalendario::class, 'criado_por');
    }

    /**
     * Relacionamento: Cargos de Grande Assembleia atribuídos pelo usuário
     */
    public function cargosAtribuidos(): HasMany
    {
        return $this->hasMany(CargoGrandeAssembleia::class, 'atribuido_por');
    }

    /**
     * Relacionamento: Anexos de protocolo enviados pelo usuário
     */
    public function protocoloAnexosEnviados(): HasMany
    {
        return $this->hasMany(ProtocoloAnexo::class, 'uploaded_by');
    }

    /**
     * Relacionamento: Anexos de ticket enviados pelo usuário
     */
    public function ticketAnexosEnviados(): HasMany
    {
        return $this->hasMany(TicketAnexo::class, 'uploaded_by');
    }

    /**
     * Verifica se o usuário é membro de uma assembleia
     */
    public function isMembro(): bool
    {
        return $this->membro()->exists();
    }

    /**
     * Verifica se o usuário tem cargo administrativo
     */
    public function hasCargoAdministrativo(): bool
    {
        return $this->hasRole(['super_admin', 'admin', 'coordenadora']);
    }

    /**
     * Verifica se o usuário é de um tipo específico
     */
    public function isTipoUsuario(string $codigo): bool
    {
        return $this->tipoUsuario?->codigo === $codigo;
    }

    /**
     * Verifica se o usuário é Menina Ativa
     */
    public function isMeninaAtiva(): bool
    {
        return $this->isTipoUsuario(TipoUsuario::MENINA_ATIVA);
    }

    /**
     * Verifica se o usuário é Maioridade
     */
    public function isMaioridade(): bool
    {
        return $this->isTipoUsuario(TipoUsuario::MAIORIDADE);
    }

    /**
     * Verifica se o usuário é Tio Maçom
     */
    public function isTioMacom(): bool
    {
        return $this->isTipoUsuario(TipoUsuario::TIO_MACOM);
    }

    /**
     * Verifica se o usuário é Tia Estrela do Oriente
     */
    public function isTiaEstrela(): bool
    {
        return $this->isTipoUsuario(TipoUsuario::TIA_ESTRELA);
    }

    /**
     * Verifica se o usuário é Tio
     */
    public function isTio(): bool
    {
        return $this->isTipoUsuario(TipoUsuario::TIO);
    }

    /**
     * Verifica se o usuário é Tia
     */
    public function isTia(): bool
    {
        return $this->isTipoUsuario(TipoUsuario::TIA);
    }

    /**
     * Verifica se o usuário é Admin da Assembleia
     */
    public function isAdminAssembleia(): bool
    {
        return $this->hasAnyRole(['admin_assembleia', 'digna_matrona', 'vice_digna_matrona']);
    }

    /**
     * Verifica se o usuário é Membro da Jurisdição
     */
    public function isMembroJurisdicao(): bool
    {
        return $this->hasAnyRole(['membro_jurisdicao', 'gra_digna', 'vice_gra_digna']);
    }

    /**
     * Verifica se o usuário é Membro comum
     */
    public function isMembroComum(): bool
    {
        return !$this->isAdminAssembleia() && !$this->isMembroJurisdicao();
    }

    /**
     * Obtém o nome do tipo de usuário
     */
    public function getNomeTipoUsuario(): ?string
    {
        return $this->tipoUsuario?->nome;
    }

    /**
     * Verifica se o usuário pode criar protocolos
     */
    public function podecriarProtocolos(): bool
    {
        return $this->isAdminAssembleia() || $this->isMembroJurisdicao();
    }

    /**
     * Verifica se o usuário pode aprovar protocolos
     */
    public function podeAprovarProtocolos(): bool
    {
        return $this->isMembroJurisdicao() || $this->hasRole('presidente_honrarias');
    }

    /**
     * Verifica se o usuário precisa alterar a senha
     */
    public function needsPasswordChange(): bool
    {
        // Refresh from database to ensure we have latest data
        $this->refresh();

        // If password_changed_at is null, user needs to change password
        // This is the primary indicator that the user hasn't changed their temporary password
        if (is_null($this->password_changed_at)) {
            return true;
        }

        // If password_changed_at is set, the user has already changed their password
        // and doesn't need to change it again
        return false;
    }

    /**
     * Verifica se o usuário (membro) está ativa
     */
    public function isMembroAtiva(): bool
    {
        if (!$this->membro) {
            // Se não tem perfil de membro, considera como "ativo"
            return true;
        }

        // Retorna true se o status for candidata, ativa ou maioridade
        return in_array($this->membro->status, ['candidata', 'ativa', 'maioridade']);
    }

    /**
     * Mark that the user has changed their password
     */
    public function markPasswordChanged(): void
    {
        $this->update([
            'email_verified_at' => $this->email_verified_at ?? now(), // Only set if not already set
            'password_changed_at' => now(),
        ]);
        
        // Force refresh the model to ensure changes are loaded
        $this->refresh();
    }
}
