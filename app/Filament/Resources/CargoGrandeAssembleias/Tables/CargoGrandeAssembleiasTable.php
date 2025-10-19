<?php

namespace App\Filament\Resources\CargoGrandeAssembleias\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CargoGrandeAssembleiasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('membro.nome')
                    ->label('Membro')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('membro.assembleia.nome')
                    ->label('Assembleia')
                    ->searchable()
                    ->toggleable(),
                    
                TextColumn::make('tipoCargo.nome')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('data_inicio')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                TextColumn::make('data_fim')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Indefinido'),
                    
                TextColumn::make('atribuido_por')
                    ->label('Atribuído por')
                    ->searchable()
                    ->limit(30),
                    
                IconColumn::make('ativo')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                TextColumn::make('observacoes')
                    ->label('Observações')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipoCargo')
                    ->label('Tipo de Cargo')
                    ->relationship('tipoCargo', 'nome')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('assembleia')
                    ->label('Assembleia')
                    ->relationship('membro.assembleia', 'nome')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('ativo')
                    ->label('Status')
                    ->options([
                        1 => 'Ativo',
                        0 => 'Inativo',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] !== null,
                            fn (Builder $query, $value): Builder => $query->where('ativo', (bool) $data['value']),
                        );
                    }),
                    
                SelectFilter::make('atribuido_por')
                    ->label('Atribuído por')
                    ->options(function () {
                        return \App\Models\CargoGrandeAssembleia::query()
                            ->distinct()
                            ->pluck('atribuido_por', 'atribuido_por')
                            ->filter()
                            ->toArray();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('membro.nome');
    }
}
