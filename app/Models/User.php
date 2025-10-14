<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasUuid;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuid;

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
        ];
    }

    /**
     * Verifica se o usuário pode acessar o painel Filament
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Ajustar conforme necessário
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
}
