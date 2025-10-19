<?php

namespace App\Filament\Resources\CargoAssembleias\Schemas;

use App\Models\Assembleia;
use App\Models\Membro;
use App\Models\TipoCargoAssembleia;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CargoAssembleiaForm
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
                    ->preload(),
                    
                Select::make('tipo_cargo_id')
                    ->label('Tipo de Cargo')
                    ->relationship('tipoCargo', 'nome')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Select::make('membro_id')
                    ->label('Membro')
                    ->relationship('membro', 'nome')
                    ->searchable()
                    ->preload()
                    ->helperText('Apenas membros ativos da assembleia selecionada'),
                    
                DatePicker::make('data_inicio')
                    ->label('Data de Início')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->default(now())
                    ->maxDate(now()->addYear()),
                    
                DatePicker::make('data_fim')
                    ->label('Data de Fim')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->helperText('Deixe em branco para cargo indefinido'),
                    
                Toggle::make('ativo')
                    ->label('Cargo Ativo')
                    ->default(true)
                    ->helperText('Apenas um membro pode ter o mesmo cargo ativo em uma assembleia'),
                    
                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->maxLength(500)
                    ->placeholder('Observações sobre este cargo...')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
