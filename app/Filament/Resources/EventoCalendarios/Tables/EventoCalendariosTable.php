<?php

namespace App\Filament\Resources\EventoCalendarios\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventoCalendariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('assembleia.nome')
                    ->label('Assembleia')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'reuniao_ordinaria' => 'Reunião Ordinária',
                        'reuniao_extraordinaria' => 'Reunião Extraordinária',
                        'assembleia_geral' => 'Assembleia Geral',
                        'sessao_solene' => 'Sessão Solene',
                        'palestra' => 'Palestra',
                        'workshop' => 'Workshop',
                        'evento_social' => 'Evento Social',
                        'outros' => 'Outros',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'reuniao_ordinaria' => 'primary',
                        'reuniao_extraordinaria' => 'warning',
                        'assembleia_geral' => 'danger',
                        'sessao_solene' => 'success',
                        'palestra' => 'info',
                        'workshop' => 'purple',
                        'evento_social' => 'pink',
                        'outros' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('data_inicio')
                    ->label('Data/Hora Início')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('data_fim')
                    ->label('Data/Hora Fim')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('local')
                    ->label('Local')
                    ->searchable()
                    ->limit(30),

                IconColumn::make('publico')
                    ->label('Público')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),

                ColorColumn::make('cor_evento')
                    ->label('Cor'),

                TextColumn::make('criadoPor.name')
                    ->label('Criado por')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('assembleia_id')
                    ->label('Assembleia')
                    ->relationship('assembleia', 'nome')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('tipo')
                    ->label('Tipo de Evento')
                    ->options([
                        'reuniao_ordinaria' => 'Reunião Ordinária',
                        'reuniao_extraordinaria' => 'Reunião Extraordinária',
                        'assembleia_geral' => 'Assembleia Geral',
                        'sessao_solene' => 'Sessão Solene',
                        'palestra' => 'Palestra',
                        'workshop' => 'Workshop',
                        'evento_social' => 'Evento Social',
                        'outros' => 'Outros',
                    ]),

                Filter::make('publico')
                    ->label('Apenas Públicos')
                    ->query(fn (Builder $query): Builder => $query->where('publico', true)),

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
            ])
            ->defaultSort('data_inicio', 'asc');
    }
}
