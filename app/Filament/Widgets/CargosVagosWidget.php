<?php

namespace App\Filament\Widgets;

use App\Models\TipoCargoAssembleia;
use App\Models\CargoAssembleia;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CargosVagosWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TipoCargoAssembleia::query()
                    ->whereNotIn('id', function ($query) {
                        $query->select('tipo_cargo_id')
                            ->from('cargos_assembleia')
                            ->where('ativo', true);
                    })
                    ->where('ativo', true)
                    ->orderBy('categoria')
                    ->orderBy('ordem')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Cargo')
                    ->searchable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('categoria')
                    ->label('Categoria')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'administrativo' => 'danger',
                        'menina' => 'warning',
                        'grande_assembleia' => 'success',
                        default => 'secondary',
                    }),
                    
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-minus'),
                    
                Tables\Columns\TextColumn::make('ordem')
                    ->label('Prioridade')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(50)
                    ->placeholder('Sem descrição'),
            ]);
    }
}