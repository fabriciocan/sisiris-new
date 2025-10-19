<?php

namespace App\Filament\Resources\EventoCalendarios\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class EventoCalendarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('assembleia_id')
                    ->label('Assembleia')
                    ->relationship('assembleia', 'nome')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(fn() => Auth::user()?->membro?->assembleia_id),

                TextInput::make('titulo')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('descricao')
                    ->label('Descrição')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('tipo')
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
                    ])
                    ->required()
                    ->searchable(),

                DateTimePicker::make('data_inicio')
                    ->label('Data/Hora de Início')
                    ->required()
                    ->seconds(false)
                    ->displayFormat('d/m/Y H:i'),

                DateTimePicker::make('data_fim')
                    ->label('Data/Hora de Fim')
                    ->seconds(false)
                    ->displayFormat('d/m/Y H:i')
                    ->afterOrEqual('data_inicio'),

                TextInput::make('local')
                    ->label('Local')
                    ->maxLength(255),

                Textarea::make('endereco')
                    ->label('Endereço')
                    ->rows(2),

                Toggle::make('publico')
                    ->label('Evento Público')
                    ->helperText('Marque se o evento deve ser visível para todos')
                    ->default(false),

                ColorPicker::make('cor_evento')
                    ->label('Cor do Evento')
                    ->hex()
                    ->default('#3B82F6')
                    ->helperText('Escolha uma cor para identificar o evento no calendário'),

                Hidden::make('criado_por')
                    ->default(Auth::id()),
            ])
            ->columns(2);
    }
}
