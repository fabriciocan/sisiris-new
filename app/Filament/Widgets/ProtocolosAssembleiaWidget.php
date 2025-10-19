<?php

namespace App\Filament\Widgets;

use App\Models\Protocolo;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ProtocolosAssembleiaWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 4;
    public function table(Table $table): Table
    {
        $user = Auth::user();
        $assembleia_id = $user->membro?->assembleia_id;
        
        $query = Protocolo::query()
            ->with(['assembleia', 'solicitante'])
            ->when($assembleia_id, function ($query) use ($assembleia_id) {
                return $query->where('assembleia_id', $assembleia_id);
            })
            ->where('status', '!=', 'concluido')
            ->orderBy('created_at', 'desc')
            ->limit(10);

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'requerimento' => 'info',
                        'recurso' => 'warning',
                        'denuncia' => 'danger',
                        'consulta' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('assunto')
                    ->label('Assunto')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendente' => 'warning',
                        'em_analise' => 'info',
                        'aguardando_documentos' => 'gray',
                        'concluido' => 'success',
                        'arquivado' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('solicitante.name')
                    ->label('Solicitante')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}