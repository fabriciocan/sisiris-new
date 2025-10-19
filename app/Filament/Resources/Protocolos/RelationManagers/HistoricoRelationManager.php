<?php

namespace App\Filament\Resources\Protocolos\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Illuminate\Database\Eloquent\Builder;

class HistoricoRelationManager extends RelationManager
{
    protected static string $relationship = 'historico';

    protected static ?string $title = 'Histórico do Protocolo';

    protected static ?string $modelLabel = 'Registro';

    protected static ?string $pluralModelLabel = 'Registros';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('acao')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                    
                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->placeholder('Sistema'),
                    
                TextColumn::make('acao')
                    ->label('Ação')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'criacao' => 'Criação',
                        'edicao' => 'Edição',
                        'aprovacao' => 'Aprovação',
                        'rejeicao' => 'Rejeição',
                        'transicao_etapa' => 'Mudança de Etapa',
                        'processamento' => 'Processamento',
                        'conclusao' => 'Conclusão',
                        'cancelamento' => 'Cancelamento',
                        default => $state ? ucfirst($state) : 'N/A',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'criacao' => 'success',
                        'edicao' => 'info',
                        'aprovacao' => 'success',
                        'rejeicao' => 'danger',
                        'transicao_etapa' => 'warning',
                        'processamento' => 'info',
                        'conclusao' => 'success',
                        'cancelamento' => 'danger',
                        default => 'gray',
                    }),
                    
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(100)
                    ->wrap()
                    ->placeholder('N/A'),
                    
                TextColumn::make('status_mudanca')
                    ->label('Mudança de Status')
                    ->getStateUsing(function ($record): ?string {
                        if ($record->status_anterior && $record->status_novo) {
                            return "{$record->status_anterior} → {$record->status_novo}";
                        }
                        return null;
                    })
                    ->placeholder('N/A')
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('etapa_mudanca')
                    ->label('Mudança de Etapa')
                    ->getStateUsing(function ($record): ?string {
                        if ($record->etapa_anterior && $record->etapa_nova) {
                            return "{$record->etapa_anterior} → {$record->etapa_nova}";
                        }
                        return null;
                    })
                    ->placeholder('N/A')
                    ->badge()
                    ->color('warning'),
                    
                TextColumn::make('comentario')
                    ->label('Comentário')
                    ->limit(100)
                    ->wrap()
                    ->placeholder('N/A'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Não permitir criar registros manualmente
            ])
            ->actions([
                // Não permitir editar ou excluir registros
            ])
            ->bulkActions([
                // Não permitir ações em massa
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->poll('30s'); // Atualizar a cada 30 segundos
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['user']);
    }
}