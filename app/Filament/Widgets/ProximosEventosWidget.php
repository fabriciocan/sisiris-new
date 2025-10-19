<?php

namespace App\Filament\Widgets;

use App\Models\EventoCalendario;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProximosEventosWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EventoCalendario::query()
                    ->where('data_inicio', '>=', now())
                    ->where('data_inicio', '<=', now()->addDays(7))
                    ->with(['assembleia'])
                    ->orderBy('data_inicio')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Evento')
                    ->searchable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('assembleia.nome')
                    ->label('Assembleia')
                    ->default('Jurisdição')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reuniao_ordinaria' => 'primary',
                        'reuniao_extraordinaria' => 'warning',
                        'iniciacao' => 'success',
                        'sessao_magna' => 'danger',
                        'evento_social' => 'info',
                        default => 'secondary',
                    }),
                    
                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('local')
                    ->label('Local')
                    ->limit(30)
                    ->placeholder('Não informado'),
                    
                Tables\Columns\IconColumn::make('publico')
                    ->label('Público')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash'),
            ]);
    }
}