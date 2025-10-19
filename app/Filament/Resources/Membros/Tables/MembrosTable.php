<?php

namespace App\Filament\Resources\Membros\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MembrosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_membro')
                    ->label('Membro')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('assembleia.nome')
                    ->label('Assembleia')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nome_completo')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('data_nascimento')
                    ->label('Nascimento')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cpf')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('telefone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nome_mae')
                    ->label('Nome da Mãe')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('telefone_mae')
                    ->label('Tel. Mãe')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nome_pai')
                    ->label('Nome do Pai')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('telefone_pai')
                    ->label('Tel. Pai')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('responsavel_legal')
                    ->label('Responsável Legal')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('contato_responsavel')
                    ->label('Contato Responsável')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('data_iniciacao')
                    ->label('Iniciação')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('madrinha')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('data_maioridade')
                    ->label('Maioridade')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('honrarias_count')
                    ->label('Qtd. Honrarias')
                    ->counts('honrarias')
                    ->badge()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('honrarias_resumo')
                    ->label('Honrarias')
                    ->getStateUsing(function ($record) {
                        $count = $record->honrarias->count();
                        if ($count === 0) {
                            return 'Nenhuma';
                        }
                        
                        $tipos = $record->honrarias->pluck('tipo_honraria')->unique()->map(function ($tipo) {
                            return match($tipo) {
                                'coracao_cores' => 'CC',
                                'grande_cruz_cores' => 'GC',
                                'homenageados_ano' => 'HA',
                                default => $tipo
                            };
                        })->join(', ');
                        
                        return $count . ' (' . $tipos . ')';
                    })
                    ->badge()
                    ->color(function ($state, $record) {
                        if ($record->honrarias->isEmpty()) {
                            return 'gray';
                        }
                        return 'success';
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('foto')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Excluído em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
