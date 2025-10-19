<?php

namespace App\Filament\Resources\Tickets\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class AvaliarTicketAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'avaliar')
            ->label('Avaliar Atendimento')
            ->icon('heroicon-o-star')
            ->color('warning')
            ->visible(fn(Ticket $record) => 
                $record->status === 'fechado' && 
                $record->solicitante_id === Auth::id() &&
                !$record->avaliacao
            )
            ->form([
                Select::make('avaliacao')
                    ->label('Nota de Qualidade')
                    ->options([
                        1 => '⭐ (1) - Muito Insatisfeito',
                        2 => '⭐⭐ (2) - Insatisfeito',
                        3 => '⭐⭐⭐ (3) - Neutro',
                        4 => '⭐⭐⭐⭐ (4) - Satisfeito',
                        5 => '⭐⭐⭐⭐⭐ (5) - Muito Satisfeito',
                    ])
                    ->required()
                    ->helperText('Avalie a qualidade do atendimento recebido'),

                Textarea::make('comentario_avaliacao')
                    ->label('Comentários (Opcional)')
                    ->placeholder('Deixe seus comentários sobre o atendimento...')
                    ->rows(3),

                TextInput::make('tempo_resolucao_info')
                    ->label('Tempo de Resolução')
                    ->disabled()
                    ->default(fn(Ticket $record) => 
                        $record->prazo_sla && $record->data_fechamento && $record->data_fechamento <= $record->prazo_sla 
                            ? 'Resolvido dentro do prazo ✅' 
                            : 'Ultrapassou o prazo ⚠️'
                    ),
            ])
            ->action(function (array $data, Ticket $record): void {
                $record->update([
                    'avaliacao' => $data['avaliacao'],
                    'comentario_avaliacao' => $data['comentario_avaliacao'] ?? null,
                ]);

                // Aqui seria possível enviar notificação para o responsável
                // sobre a avaliação recebida
            });
    }
}