<?php

namespace App\Filament\Widgets;

use App\Models\EventoCalendario;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ProximosEventosAssembleiaWidget extends BaseWidget
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
            ->orderBy('data_inicio', 'asc')
            ->limit(10);

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('local')
                    ->label('Local')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\IconColumn::make('confirmacao_presenca')
                    ->label('Confirmação')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->defaultSort('data_inicio', 'asc');
    }
}