<?php

namespace App\Filament\Resources\Membros\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use App\Models\HonrariaMembro;

class MembroForm
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
                TextInput::make('nome_completo')
                    ->required(),
                DatePicker::make('data_nascimento')
                    ->required(),
                TextInput::make('cpf'),
                TextInput::make('telefone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                Textarea::make('endereco_completo')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('nome_mae')
                    ->required(),
                TextInput::make('telefone_mae')
                    ->tel()
                    ->required(),
                TextInput::make('nome_pai'),
                TextInput::make('telefone_pai')
                    ->tel(),
                TextInput::make('responsavel_legal'),
                TextInput::make('contato_responsavel')
                    ->required(),
                DatePicker::make('data_iniciacao'),
                TextInput::make('madrinha')
                    ->required(),
                DatePicker::make('data_maioridade'),
                Select::make('status')
                    ->options([
            'candidata' => 'Candidata',
            'ativa' => 'Ativa',
            'afastada' => 'Afastada',
            'maioridade' => 'Maioridade',
            'desligada' => 'Desligada',
        ])
                    ->required(),
                Textarea::make('motivo_afastamento')
                    ->columnSpanFull(),
                
                Repeater::make('honrarias')
                    ->label('Honrarias')
                    ->relationship()
                    ->schema([
                        Select::make('tipo_honraria')
                            ->label('Tipo de Honraria')
                            ->options(function ($livewire) {
                                $record = $livewire->getRecord();
                                $allOptions = [
                                    'coracao_cores' => 'Coração das Cores',
                                    'grande_cruz_cores' => 'Grande Cruz das Cores',
                                    'homenageados_ano' => 'Homenageados do Ano',
                                ];
                                
                                // Se não é um registro existente, retorna todas as opções
                                if (!$record || !$record->exists) {
                                    return $allOptions;
                                }
                                
                                // Verifica quais honrarias únicas o membro já possui
                                $honrariasExistentes = $record->honrarias()
                                    ->whereIn('tipo_honraria', ['coracao_cores', 'grande_cruz_cores'])
                                    ->pluck('tipo_honraria')
                                    ->toArray();
                                
                                // Remove as opções de honrarias únicas já concedidas
                                foreach ($honrariasExistentes as $honrariaExistente) {
                                    unset($allOptions[$honrariaExistente]);
                                }
                                
                                return $allOptions;
                            })
                            ->helperText(function ($livewire) {
                                $record = $livewire->getRecord();
                                if (!$record || !$record->exists) {
                                    return 'Coração das Cores e Grande Cruz das Cores só podem ser recebidas uma vez na vida.';
                                }
                                
                                $honrariasExistentes = $record->honrarias()
                                    ->whereIn('tipo_honraria', ['coracao_cores', 'grande_cruz_cores'])
                                    ->get()
                                    ->map(function ($h) {
                                        return match($h->tipo_honraria) {
                                            'coracao_cores' => 'Coração das Cores',
                                            'grande_cruz_cores' => 'Grande Cruz das Cores',
                                            default => $h->tipo_honraria
                                        };
                                    })
                                    ->toArray();
                                
                                if (empty($honrariasExistentes)) {
                                    return 'Coração das Cores e Grande Cruz das Cores só podem ser recebidas uma vez na vida.';
                                }
                                
                                return 'Honrarias únicas já recebidas: ' . implode(', ', $honrariasExistentes);
                            })
                            ->required(),
                        
                        TextInput::make('ano_recebimento')
                            ->label('Ano de Recebimento')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y'))
                            ->required(),
                        
                        Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(2),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->itemLabel(fn (array $state): ?string => 
                        isset($state['tipo_honraria'], $state['ano_recebimento']) 
                            ? ucwords(str_replace('_', ' ', $state['tipo_honraria'])) . ' - ' . $state['ano_recebimento']
                            : 'Nova Honraria'
                    )
                    ->addActionLabel('Adicionar Honraria')
                    ->reorderable(false)
                    ->columnSpanFull()
                    ->rules([
                        function ($livewire) {
                            return function (string $attribute, $value, \Closure $fail) use ($livewire) {
                                if (!is_array($value)) return;
                                
                                $record = $livewire->getRecord();
                                if (!$record || !$record->exists) return;
                                
                                // Verifica se está tentando adicionar honrarias únicas já existentes
                                $honrariasExistentes = $record->honrarias()
                                    ->whereIn('tipo_honraria', ['coracao_cores', 'grande_cruz_cores'])
                                    ->pluck('tipo_honraria')
                                    ->toArray();
                                
                                foreach ($value as $honraria) {
                                    if (isset($honraria['tipo_honraria']) && 
                                        in_array($honraria['tipo_honraria'], ['coracao_cores', 'grande_cruz_cores']) &&
                                        in_array($honraria['tipo_honraria'], $honrariasExistentes)) {
                                        
                                        $nomeHonraria = match($honraria['tipo_honraria']) {
                                            'coracao_cores' => 'Coração das Cores',
                                            'grande_cruz_cores' => 'Grande Cruz das Cores',
                                            default => $honraria['tipo_honraria']
                                        };
                                        
                                        $fail("O membro já possui a honraria '{$nomeHonraria}'. Esta honraria só pode ser recebida uma vez na vida.");
                                        break;
                                    }
                                }
                            };
                        }
                    ]),
                    
                TextInput::make('foto'),
            ]);
    }
}
