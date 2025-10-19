<?php

namespace App\Filament\Widgets;

use App\Models\EventoCalendario;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ProximosEventosMembroWidget extends BaseWidget
{
    public function table(Table $table): Table
    {
        $user = Auth::user();
        $assembleia_id = $user->membro?->assembleia_id;
        
        $query = EventoCalendario::query()
            ->when($assembleia_id, function ($query) use ($assembleia_id) {
                return $query->where('assembleia_id', $assembleia_id);
            })
            ->where('data_inicio', '>=', Carbon::now())
            ->where(function ($query) use ($user) {
                // Eventos públicos ou eventos onde o usuário foi convidado
                $query->where('publico', true)
                      ->orWhereHas('participantes', function ($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->orderBy('data_inicio', 'asc')
            ->limit(5);

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hora_inicio')
                    ->label('Hora')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'assembleia' => 'info',
                        'iniciacao' => 'success',
                        'elevacao' => 'warning',
                        'exaltacao' => 'danger',
                        'reuniao' => 'gray',
                        'evento_social' => 'purple',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('local')
                    ->label('Local')
                    ->limit(20),
            ])
            ->defaultSort('data_inicio', 'asc');
    }
}