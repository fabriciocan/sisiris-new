<?php

namespace App\Filament\Resources\Protocolos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Membro;
use App\Models\TipoUsuario;

class IniciacaoProtocoloForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações do Protocolo')
                    ->description('Dados básicos do protocolo de iniciação')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('numero_protocolo')
                                    ->label('Número do Protocolo')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(fn () => static::generateProtocolNumber())
                                    ->helperText('Gerado automaticamente'),
                                    
                                Select::make('assembleia_id')
                                    ->label('Assembleia')
                                    ->relationship('assembleia', 'nome')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->default(function () {
                                        /** @var User|null $user */
                                        $user = Auth::user();
                                        if ($user && $user->hasRole('admin_assembleia') && $user->membro) {
                                            return $user->membro->assembleia_id;
                                        }
                                        return null;
                                    })
                                    ->disabled(function () {
                                        /** @var User|null $user */
                                        $user = Auth::user();
                                        return $user && $user->hasRole('admin_assembleia');
                                    })
                                    ->dehydrated()
                                    ->reactive(),
                            ]),
                            
                        Hidden::make('tipo_protocolo')
                            ->default('iniciacao'),
                            
                        Hidden::make('solicitante_id')
                            ->default(fn () => Auth::id()),
                            

                    ]),
                    
                Section::make('Novas Meninas para Iniciação')
                    ->description('Cadastre as informações das novas meninas que serão iniciadas')
                    ->schema([
                        Repeater::make('novas_meninas')
                            ->label('Meninas para Iniciação')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('nome_completo')
                                            ->label('Nome Completo')
                                            ->required()
                                            ->maxLength(255),
                                            
                                        DatePicker::make('data_nascimento')
                                            ->label('Data de Nascimento')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->maxDate(now()->subYears(10))
                                            ->helperText('Deve ter pelo menos 10 anos'),
                                    ]),
                                    
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('cpf')
                                            ->label('CPF')
                                            ->required()
                                            ->mask('999.999.999-99')
                                            ->unique(table: 'membros', column: 'cpf', ignoreRecord: true)
                                            ->helperText('Apenas números'),
                                            
                                        TextInput::make('telefone')
                                            ->label('Telefone')
                                            ->required()
                                            ->mask('(99) 99999-9999')
                                            ->maxLength(20),
                                    ]),
                                    
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('email')
                                            ->label('E-mail')
                                            ->required()
                                            ->email()
                                            ->unique(table: 'membros', column: 'email', ignoreRecord: true)
                                            ->maxLength(255),
                                            
                                        DatePicker::make('data_iniciacao')
                                            ->label('Data de Iniciação')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->maxDate(now())
                                            ->helperText('Data da cerimônia de iniciação'),
                                    ]),
                                    
                                Textarea::make('endereco_completo')
                                    ->label('Endereço Completo')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                    
                                Section::make('Informações dos Pais/Responsáveis')
                                    ->description('Obrigatório para meninas menores de 18 anos')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('nome_mae')
                                                    ->label('Nome da Mãe')
                                                    ->required()
                                                    ->maxLength(255),
                                                    
                                                TextInput::make('telefone_mae')
                                                    ->label('Telefone da Mãe')
                                                    ->required()
                                                    ->mask('(99) 99999-9999')
                                                    ->maxLength(20),
                                            ]),
                                            
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('nome_pai')
                                                    ->label('Nome do Pai')
                                                    ->maxLength(255),
                                                    
                                                TextInput::make('telefone_pai')
                                                    ->label('Telefone do Pai')
                                                    ->mask('(99) 99999-9999')
                                                    ->maxLength(20),
                                            ]),
                                            
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('responsavel_legal')
                                                    ->label('Responsável Legal (se diferente dos pais)')
                                                    ->maxLength(255),
                                                    
                                                TextInput::make('contato_responsavel')
                                                    ->label('Contato do Responsável')
                                                    ->mask('(99) 99999-9999')
                                                    ->maxLength(20),
                                            ]),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                                    
                                Select::make('madrinha_id')
                                    ->label('Madrinha')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->options(function (Get $get) {
                                        $assembleiaId = $get('../../assembleia_id');
                                        if (!$assembleiaId) {
                                            return [];
                                        }
                                        
                                        return Membro::ativas()
                                            ->where('assembleia_id', $assembleiaId)
                                            ->whereIn('status', ['ativa', 'maioridade'])
                                            ->pluck('nome_completo', 'id')
                                            ->toArray();
                                    })
                                    ->helperText('Selecione um membro ativo da assembleia como madrinha')
                                    ->columnSpanFull(),
                                    

                            ])
                            ->itemLabel(fn (array $state): ?string => $state['nome_completo'] ?? 'Nova Menina')
                            ->addActionLabel('Adicionar Nova Menina')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->minItems(1)
                            ->maxItems(20)
                            ->columnSpanFull(),
                    ]),
                    
                Hidden::make('status')
                    ->default('em_analise'),
                    
                Hidden::make('etapa_atual')
                    ->default('aprovacao'),
            ]);
    }

    protected static function generateProtocolNumber(): string
    {
        $year = date('Y');
        $lastProtocol = \App\Models\Protocolo::whereYear('created_at', $year)
            ->where('tipo_protocolo', 'iniciacao')
            ->count();
        $number = str_pad($lastProtocol + 1, 3, '0', STR_PAD_LEFT);
        
        return "PR-INI-{$year}-{$number}";
    }
}