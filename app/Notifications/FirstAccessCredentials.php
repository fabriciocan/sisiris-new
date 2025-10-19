<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FirstAccessCredentials extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $temporaryPassword;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $temporaryPassword)
    {
        $this->temporaryPassword = $temporaryPassword;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = config('app.url') . '/admin/login';

        return (new MailMessage)
            ->subject('Bem-vinda! Suas credenciais de acesso')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Parabéns pela sua iniciação! Agora você tem acesso ao sistema da nossa organização.')
            ->line('Suas credenciais de primeiro acesso são:')
            ->line("**E-mail:** {$notifiable->email}")
            ->line("**Senha temporária:** {$this->temporaryPassword}")
            ->line('**IMPORTANTE:** Por segurança, você será solicitada a alterar sua senha no primeiro acesso.')
            ->action('Acessar o Sistema', $loginUrl)
            ->line('Se você tiver dificuldades para acessar, entre em contato com a administração da sua assembleia.')
            ->line('Bem-vinda à nossa família!')
            ->salutation('Com carinho,')
            ->salutation('Administração do Sistema');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'first_access_credentials',
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'sent_at' => now(),
        ];
    }
}