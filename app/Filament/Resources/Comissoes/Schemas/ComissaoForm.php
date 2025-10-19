<?php

namespace App\Filament\Resources\Comissoes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ComissaoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('jurisdicao_id')
                    ->label('Jurisdição')
                    ->relationship('jurisdicao', 'nome')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Selecione a jurisdição responsável por esta comissão'),
                    
                TextInput::make('nome')
                    ->label('Nome da Comissão')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex: Comissão de Finanças, Comissão de Eventos...'),
                    
                Textarea::make('descricao')
                    ->label('Descrição')
                    ->rows(3)
                    ->maxLength(500)
                    ->placeholder('Descreva as responsabilidades e objetivos desta comissão...')
                    ->columnSpanFull(),
                    
                Toggle::make('ativa')
                    ->label('Comissão Ativa')
                    ->default(true)
                    ->helperText('Comissões inativas não aparecerão para seleção'),
            ])
            ->columns(2);
    }
}
