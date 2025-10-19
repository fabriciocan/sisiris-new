<?php

namespace App\Filament\Resources\Protocolos\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Protocolos\ProtocoloResource;

class ProtocolosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_protocolo')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Número copiado!')
                    ->weight('bold'),
                    
                TextColumn::make('assembleia.nome')
                    ->label('Assembleia')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('tipo_protocolo')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'iniciacao' => 'Iniciação',
                        'transferencia' => 'Transferência',
                        'afastamento' => 'Afastamento',
                        'retorno' => 'Retorno',
                        'maioridade' => 'Maioridade',
                        'desligamento' => 'Desligamento',
                        'premios_honrarias' => 'Prêmios/Honrarias',
                        'homenageados_ano' => 'Homenageados do Ano',
                        'coracao_cores' => 'Coração das Cores',
                        'grande_cruz_cores' => 'Grande Cruz das Cores',
                        'novos_cargos_assembleia' => 'Novos Cargos Assembleia',
                        'novos_cargos_conselho' => 'Novos Cargos Conselho',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'iniciacao' => 'success',
                        'transferencia' => 'info',
                        'afastamento' => 'warning',
                        'retorno' => 'success',
                        'maioridade' => 'primary',
                        'desligamento' => 'danger',
                        'premios_honrarias' => 'purple',
                        default => 'gray',
                    }),
                    
                TextColumn::make('etapa_atual')
                    ->label('Etapa')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'criacao' => 'Criação',
                        'aprovacao' => 'Aguardando Aprovação',
                        'aprovacao_honrarias' => 'Aprovação Honrarias',
                        'definir_taxas' => 'Definir Taxas',
                        'aguardando_pagamento' => 'Aguardando Pagamento',
                        'aprovacao_final' => 'Aprovação Final',
                        'concluido' => 'Concluído',
                        'rejeitado' => 'Rejeitado',
                        default => $state ? ucfirst(str_replace('_', ' ', $state)) : 'N/A',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'criacao' => 'gray',
                        'aprovacao' => 'warning',
                        'aprovacao_honrarias' => 'info',
                        'definir_taxas' => 'warning',
                        'aguardando_pagamento' => 'warning',
                        'aprovacao_final' => 'info',
                        'concluido' => 'success',
                        'rejeitado' => 'danger',
                        default => 'gray',
                    }),
                    
                TextColumn::make('solicitante.name')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'rascunho' => 'Rascunho',
                        'pendente' => 'Pendente',
                        'em_analise' => 'Em Análise',
                        'aprovado' => 'Aprovado',
                        'rejeitado' => 'Rejeitado',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'rascunho' => 'gray',
                        'pendente' => 'warning',
                        'em_analise' => 'info',
                        'aprovado' => 'success',
                        'rejeitado' => 'danger',
                        'concluido' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),
                    
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('data_solicitacao')
                    ->label('Solicitado em')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                TextColumn::make('data_conclusao')
                    ->label('Concluído em')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Em andamento')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('assembleia')
                    ->label('Assembleia')
                    ->relationship('assembleia', 'nome')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('tipo_protocolo')
                    ->label('Tipo')
                    ->options([
                        'iniciacao' => 'Iniciação',
                        'transferencia' => 'Transferência',
                        'afastamento' => 'Afastamento',
                        'retorno' => 'Retorno',
                        'maioridade' => 'Maioridade',
                        'desligamento' => 'Desligamento',
                        'premios_honrarias' => 'Prêmios/Honrarias',
                        'homenageados_ano' => 'Homenageados do Ano',
                        'coracao_cores' => 'Coração das Cores',
                        'grande_cruz_cores' => 'Grande Cruz das Cores',
                        'novos_cargos_assembleia' => 'Novos Cargos Assembleia',
                        'novos_cargos_conselho' => 'Novos Cargos Conselho',
                    ]),
                    
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'rascunho' => 'Rascunho',
                        'pendente' => 'Pendente',
                        'em_analise' => 'Em Análise',
                        'aprovado' => 'Aprovado',
                        'rejeitado' => 'Rejeitado',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('etapa_atual')
                    ->label('Etapa')
                    ->options([
                        'criacao' => 'Criação',
                        'aprovacao' => 'Aguardando Aprovação',
                        'aprovacao_honrarias' => 'Aprovação Honrarias',
                        'definir_taxas' => 'Definir Taxas',
                        'aguardando_pagamento' => 'Aguardando Pagamento',
                        'aprovacao_final' => 'Aprovação Final',
                        'concluido' => 'Concluído',
                        'rejeitado' => 'Rejeitado',
                    ]),
                    
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                // Aprovar Afastamento
                Action::make('approve_afastamento')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->url(fn ($record): string => ProtocoloResource::getUrl('approve-afastamento', ['record' => $record]))
                    ->visible(function ($record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $user &&
                               $user->hasRole('membro_jurisdicao') &&
                               $record->tipo_protocolo === 'afastamento' &&
                               in_array($record->status, ['pendente', 'em_analise']);
                    })
                    ->tooltip('Aprovar protocolo de afastamento'),

                // Aprovar Maioridade
                Action::make('approve_maioridade')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->url(fn ($record): string => ProtocoloResource::getUrl('approve-maioridade', ['record' => $record]))
                    ->visible(function ($record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $user &&
                               $user->hasRole('membro_jurisdicao') &&
                               $record->tipo_protocolo === 'maioridade' &&
                               in_array($record->etapa_atual, ['aguardando_aprovacao', 'aprovacao']);
                    })
                    ->tooltip('Aprovar protocolo de maioridade'),

                // Aprovar Iniciação
                Action::make('approve_iniciacao')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->url(fn ($record): string => ProtocoloResource::getUrl('approve-iniciacao', ['record' => $record]))
                    ->visible(function ($record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $user &&
                               $user->hasRole('membro_jurisdicao') &&
                               $record->tipo_protocolo === 'iniciacao' &&
                               in_array($record->status, ['pendente', 'em_analise']);
                    })
                    ->tooltip('Aprovar protocolo de iniciação'),
            ])
            ->toolbarActions([
                // Removido DeleteBulkAction - ninguém pode excluir protocolos
            ])
            ->defaultSort('data_solicitacao', 'desc')
            ->striped();
    }
}
