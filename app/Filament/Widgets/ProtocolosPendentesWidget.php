<?php

namespace App\Filament\Widgets;

use App\Models\Protocolo;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProtocolosPendentesWidget extends BaseWidget
{

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Protocolo::query()
                    ->whereIn('status', ['pendente', 'em_analise'])
                    ->with(['assembleia', 'membro', 'solicitante'])
                    ->latest('data_solicitacao')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_protocolo')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('assembleia.nome')
                    ->label('Assembleia')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'iniciacao' => 'success',
                        'transferencia' => 'info',
                        'afastamento' => 'warning',
                        'maioridade' => 'gray',
                        'desligamento' => 'danger',
                        default => 'secondary',
                    }),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendente' => 'warning',
                        'em_analise' => 'info',
                        'aprovado' => 'success',
                        'rejeitado' => 'danger',
                        default => 'secondary',
                    }),
                    
                Tables\Columns\TextColumn::make('data_solicitacao')
                    ->label('Solicitado em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('solicitante.name')
                    ->label('Solicitante')
                    ->limit(20),
            ]);
    }
}