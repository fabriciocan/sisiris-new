<?php

namespace App\Jobs;

use App\Models\EventoCalendario;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotificarEventosProximos implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $amanha = Carbon::now()->addDay();
        
        // Buscar eventos que acontecem amanhã
        $eventosAmanha = EventoCalendario::with(['assembleia'])
            ->whereDate('data_inicio', $amanha)
            ->get();

        foreach ($eventosAmanha as $evento) {
            // Notificar usuários da assembleia do evento
            $usuarios = User::whereHas('membro', function ($query) use ($evento) {
                $query->where('assembleia_id', $evento->assembleia_id);
            })->get();

            foreach ($usuarios as $usuario) {
                // Se evento é privado, só notifica membros da assembleia
                if (!$evento->publico && $usuario->assembleia_id !== $evento->assembleia_id) {
                    continue;
                }

                Notification::make()
                    ->title('Evento Amanhã')
                    ->body("O evento '{$evento->titulo}' está agendado para amanhã às " . 
                           Carbon::parse($evento->data_inicio)->format('H:i'))
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->persistent()
                    ->sendToDatabase($usuario);
            }
        }

        // Buscar eventos que começam em 1 hora
        $proximaHora = Carbon::now()->addHour();
        $eventosProximaHora = EventoCalendario::with(['assembleia'])
            ->whereBetween('data_inicio', [
                $proximaHora->copy()->subMinutes(5),
                $proximaHora->copy()->addMinutes(5)
            ])
            ->get();

        foreach ($eventosProximaHora as $evento) {
            $usuarios = User::whereHas('membro', function ($query) use ($evento) {
                $query->where('assembleia_id', $evento->assembleia_id);
            })->get();

            foreach ($usuarios as $usuario) {
                if (!$evento->publico && $usuario->assembleia_id !== $evento->assembleia_id) {
                    continue;
                }

                Notification::make()
                    ->title('Evento em 1 hora')
                    ->body("O evento '{$evento->titulo}' começará em aproximadamente 1 hora")
                    ->icon('heroicon-o-clock')
                    ->color('danger')
                    ->persistent()
                    ->sendToDatabase($usuario);
            }
        }
    }
}
