<?php

namespace App\Filament\Resources\Jurisdicaos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class JurisdicaoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome da Jurisdição')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('sigla')
                    ->label('Sigla')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true),
                
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                
                TextInput::make('telefone')
                    ->label('Telefone')
                    ->tel()
                    ->required()
                    ->maxLength(20)
                    ->mask('(99) 99999-9999')
                    ->placeholder('(00) 00000-0000'),
                
                Textarea::make('endereco_completo')
                    ->label('Endereço Completo')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
                
                Toggle::make('ativa')
                    ->label('Jurisdição Ativa')
                    ->default(true),
            ]);
    }
}
