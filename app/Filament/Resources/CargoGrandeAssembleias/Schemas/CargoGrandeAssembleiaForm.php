<?php

namespace App\Filament\Resources\CargoGrandeAssembleias\Schemas;

use App\Models\Membro;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CargoGrandeAssembleiaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('membro_id')
                    ->label('Membro')
                    ->relationship('membro', 'nome')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Apenas membros ativos podem receber cargos da Grande Assembleia'),
                    
                Select::make('tipo_cargo_id')
                    ->label('Tipo de Cargo')
                    ->relationship('tipoCargo', 'nome')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                DatePicker::make('data_inicio')
                    ->label('Data de Início')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->default(now())
                    ->maxDate(now()->addYears(2)),
                    
                DatePicker::make('data_fim')
                    ->label('Data de Fim')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->helperText('Para cargos com mandato específico'),
                    
                TextInput::make('atribuido_por')
                    ->label('Atribuído por')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nome da autoridade que atribuiu o cargo')
                    ->helperText('Ex: Grão-Mestral, Grande Secretário, etc.'),
                    
                Toggle::make('ativo')
                    ->label('Cargo Ativo')
                    ->default(true)
                    ->helperText('Apenas um membro pode ter o mesmo cargo ativo'),
                    
                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->maxLength(500)
                    ->placeholder('Observações sobre este cargo de Grande Assembleia...')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
