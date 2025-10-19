<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Filament\Resources\Tickets\Actions\AvaliarTicketAction;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_ticket')
                    ->label('Ticket')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('assembleia.nome')
                    ->label('Assembleia')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('comissao.nome')
                    ->label('Comissão')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('solicitante.name')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('assunto')
                    ->label('Assunto')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('categoria')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tecnico' => 'info',
                        'administrativo' => 'warning',
                        'financeiro' => 'success',
                        'juridico' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('prioridade')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgente' => 'danger',
                        'alta' => 'warning',
                        'normal' => 'success',
                        'baixa' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aberto' => 'warning',
                        'em_atendimento' => 'info',
                        'aguardando_resposta' => 'gray',
                        'resolvido' => 'success',
                        'fechado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('data_abertura')
                    ->label('Abertura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('avaliacao')
                    ->label('Avaliação')
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('⭐', $state) : '-')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                AvaliarTicketAction::make(),
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
