<?php

namespace App\Filament\Resources\Assembleias\Schemas;

use App\Models\Jurisdicao;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AssembleiaForm
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
                    ->createOptionForm([
                        TextInput::make('nome')
                            ->label('Nome da Jurisdição')
                            ->required(),
                        TextInput::make('sigla')
                            ->label('Sigla')
                            ->required(),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required(),
                    ]),
                
                TextInput::make('numero')
                    ->label('Número da Assembleia')
                    ->required()
                    ->numeric()
                    ->unique(ignoreRecord: true)
                    ->minValue(1),
                    
                TextInput::make('nome')
                    ->label('Nome da Assembleia')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('cidade')
                    ->label('Cidade')
                    ->required()
                    ->maxLength(100),
                    
                Select::make('estado')
                    ->label('Estado')
                    ->required()
                    ->default('PR')
                    ->options([
                        'AC' => 'Acre',
                        'AL' => 'Alagoas',
                        'AP' => 'Amapá',
                        'AM' => 'Amazonas',
                        'BA' => 'Bahia',
                        'CE' => 'Ceará',
                        'DF' => 'Distrito Federal',
                        'ES' => 'Espírito Santo',
                        'GO' => 'Goiás',
                        'MA' => 'Maranhão',
                        'MT' => 'Mato Grosso',
                        'MS' => 'Mato Grosso do Sul',
                        'MG' => 'Minas Gerais',
                        'PA' => 'Pará',
                        'PB' => 'Paraíba',
                        'PR' => 'Paraná',
                        'PE' => 'Pernambuco',
                        'PI' => 'Piauí',
                        'RJ' => 'Rio de Janeiro',
                        'RN' => 'Rio Grande do Norte',
                        'RS' => 'Rio Grande do Sul',
                        'RO' => 'Rondônia',
                        'RR' => 'Roraima',
                        'SC' => 'Santa Catarina',
                        'SP' => 'São Paulo',
                        'SE' => 'Sergipe',
                        'TO' => 'Tocantins',
                    ])
                    ->searchable(),
                    
                Textarea::make('endereco_completo')
                    ->label('Endereço Completo')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
                    
                DatePicker::make('data_fundacao')
                    ->label('Data de Fundação')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(now()),
                    
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                    
                TextInput::make('telefone')
                    ->label('Telefone')
                    ->tel()
                    ->mask('(99) 99999-9999')
                    ->placeholder('(00) 00000-0000')
                    ->maxLength(20),
                    
                TextInput::make('loja_patrocinadora')
                    ->label('Loja Patrocinadora')
                    ->maxLength(255),
                    
                Toggle::make('ativa')
                    ->label('Assembleia Ativa')
                    ->default(true),
            ]);
    }
}
