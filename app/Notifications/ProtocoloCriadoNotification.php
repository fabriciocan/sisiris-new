<?php

namespace App\Notifications;

use App\Models\Protocolo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProtocoloCriadoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $protocolo;

    /**
     * Create a new notification instance.
     */
    public function __construct(Protocolo $protocolo)
    {
        $this->protocolo = $protocolo;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tipoDescricao = match($this->protocolo->tipo) {
            'requerimento' => 'Requerimento',
            'recurso' => 'Recurso',
            'denuncia' => 'Denúncia',
            'consulta' => 'Consulta',
            default => 'Protocolo'
        };

        return (new MailMessage)
            ->subject("📋 Novo {$tipoDescricao} - #{$this->protocolo->numero_protocolo}")
            ->greeting('Olá!')
            ->line("Um novo {$tipoDescricao} foi criado no sistema.")
            ->line("**Número:** {$this->protocolo->numero_protocolo}")
            ->line("**Tipo:** {$tipoDescricao}")
            ->line("**Título:** {$this->protocolo->titulo}")
            ->line("**Assembleia:** {$this->protocolo->assembleia->nome}")
            ->line("**Solicitante:** {$this->protocolo->solicitante->name}")
            ->line("**Data:** {$this->protocolo->data_solicitacao->format('d/m/Y H:i')}")
            ->when($this->protocolo->descricao, function ($message) {
                return $message->line("**Descrição:** " . substr($this->protocolo->descricao, 0, 200) . '...');
            })
            ->action('Ver Protocolo', url('/admin/protocolos/' . $this->protocolo->id))
            ->line('Por favor, analise e tome as providências necessárias.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Novo Protocolo Criado',
            'message' => "Protocolo #{$this->protocolo->numero_protocolo} ({$this->protocolo->tipo}) criado por {$this->protocolo->solicitante->name}",
            'type' => 'protocolo_criado',
            'protocolo_id' => $this->protocolo->id,
            'protocolo_numero' => $this->protocolo->numero_protocolo,
            'protocolo_tipo' => $this->protocolo->tipo,
            'solicitante_nome' => $this->protocolo->solicitante->name,
            'assembleia_nome' => $this->protocolo->assembleia->nome,
        ];
    }
}