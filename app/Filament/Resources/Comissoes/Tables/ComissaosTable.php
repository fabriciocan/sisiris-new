<?php

namespace App\Filament\Resources\Comissoes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ComissoesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jurisdicao.nome')
                    ->label('Jurisdição')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('nome')
                    ->label('Nome da Comissão')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                    
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('comissao_membros_count')
                    ->label('Membros')
                    ->counts('comissaoMembros')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
                    
                IconColumn::make('ativa')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jurisdicao')
                    ->label('Jurisdição')
                    ->relationship('jurisdicao', 'nome')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('ativa')
                    ->label('Status')
                    ->options([
                        1 => 'Ativa',
                        0 => 'Inativa',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] !== null,
                            fn (Builder $query, $value): Builder => $query->where('ativa', (bool) $data['value']),
                        );
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
            ->defaultSort('nome');
    }
}
