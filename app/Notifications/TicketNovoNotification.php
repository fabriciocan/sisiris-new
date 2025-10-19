<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNovoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
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
        $prioridadeEmoji = match($this->ticket->prioridade) {
            'baixa' => '🟢',
            'media' => '🟡',
            'alta' => '🟠',
            'urgente' => '🔴',
            default => '⚪'
        };

        return (new MailMessage)
            ->subject("🎫 Novo Ticket #{$this->ticket->id} - {$this->ticket->titulo}")
            ->greeting('Olá!')
            ->line("Um novo ticket foi criado no sistema e requer sua atenção.")
            ->line("**Número:** #{$this->ticket->id}")
            ->line("**Título:** {$this->ticket->titulo}")
            ->line("**Prioridade:** {$prioridadeEmoji} " . ucfirst($this->ticket->prioridade))
            ->line("**Categoria:** {$this->ticket->categoria}")
            ->line("**Solicitante:** {$this->ticket->solicitante->name}")
            ->line("**Data:** {$this->ticket->created_at->format('d/m/Y H:i')}")
            ->when($this->ticket->descricao, function ($message) {
                return $message->line("**Descrição:** " . substr($this->ticket->descricao, 0, 200) . '...');
            })
            ->action('Ver Ticket', url('/admin/tickets/' . $this->ticket->id))
            ->line('Por favor, analise e responda conforme necessário.')
            ->line("SLA: Resposta até " . $this->ticket->prazo_resposta?->format('d/m/Y H:i'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Novo Ticket Criado',
            'message' => "Ticket #{$this->ticket->id} - {$this->ticket->titulo} (Prioridade: {$this->ticket->prioridade})",
            'type' => 'ticket_novo',
            'ticket_id' => $this->ticket->id,
            'ticket_titulo' => $this->ticket->titulo,
            'ticket_prioridade' => $this->ticket->prioridade,
            'ticket_categoria' => $this->ticket->categoria,
            'solicitante_nome' => $this->ticket->solicitante->name,
            'prazo_resposta' => $this->ticket->prazo_resposta?->format('Y-m-d H:i:s'),
        ];
    }
}