<?php

namespace App\Notifications;

use App\Models\Membro;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class AniversarioNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $membro;
    protected $isParaAniversariante;

    /**
     * Create a new notification instance.
     */
    public function __construct(Membro $membro, bool $isParaAniversariante = false)
    {
        $this->membro = $membro;
        $this->isParaAniversariante = $isParaAniversariante;
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
        if ($this->isParaAniversariante) {
            return (new MailMessage)
                ->subject('🎂 Feliz Aniversário!')
                ->greeting('Feliz Aniversário, ' . $this->membro->nome_completo . '!')
                ->line('Hoje é um dia especial! A Grande Assembleia de Maçons do Estado do Paraná deseja a você um feliz aniversário.')
                ->line('Que este novo ano de vida seja repleto de alegrias, conquistas e muito crescimento pessoal e maçônico.')
                ->line('Parabéns e muitas felicidades!')
                ->salutation('Com fraternidade,')
                ->salutation('Sistema SISIRIS - IORG Paraná');
        }

        return (new MailMessage)
            ->subject('🎂 Aniversário de Membro - ' . $this->membro->nome_completo)
            ->greeting('Olá!')
            ->line('Hoje é aniversário do membro ' . $this->membro->nome_completo . ' da assembleia ' . $this->membro->assembleia->nome . '.')
            ->line('Data de nascimento: ' . $this->membro->data_nascimento->format('d/m/Y'))
            ->line('Considere enviar uma mensagem de felicitações ao aniversariante.')
            ->action('Ver Perfil do Membro', url('/admin/membros/' . $this->membro->id))
            ->line('Esta é uma notificação automática do sistema SISIRIS.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->isParaAniversariante) {
            return [
                'title' => 'Feliz Aniversário!',
                'message' => 'Feliz aniversário! Desejamos um ano repleto de alegrias e conquistas.',
                'type' => 'aniversario_proprio',
                'membro_id' => $this->membro->id,
                'membro_nome' => $this->membro->nome_completo,
            ];
        }

        return [
            'title' => 'Aniversário de Membro',
            'message' => "Hoje é aniversário de {$this->membro->nome_completo} ({$this->membro->assembleia->nome})",
            'type' => 'aniversario_membro',
            'membro_id' => $this->membro->id,
            'membro_nome' => $this->membro->nome_completo,
            'assembleia_nome' => $this->membro->assembleia->nome,
        ];
    }
}