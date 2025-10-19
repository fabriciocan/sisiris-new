<?php

namespace App\Filament\Resources\Protocolos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Membro;
use App\Models\TipoUsuario;

class MaioridadeProtocoloForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações do Protocolo')
                    ->description('Dados básicos do protocolo de maioridade')
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
                                    ->reactive()
                                    ->afterStateUpdated(fn (Set $set) => $set('membros_selecionados', [])),
                            ]),
                            
                        Hidden::make('tipo_protocolo')
                            ->default('maioridade'),
                            
                        Hidden::make('solicitante_id')
                            ->default(fn () => Auth::id()),
                            

                    ]),
                    
                Section::make('Seleção de Meninas Ativas')
                    ->description('Selecione as meninas ativas que participarão da cerimônia de maioridade')
                    ->schema([
                        Select::make('membros_selecionados')
                            ->label('Meninas Ativas Elegíveis')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(function (Get $get) {
                                $assembleiaId = $get('assembleia_id');
                                if (!$assembleiaId) {
                                    return [];
                                }
                                
                                return Membro::meninasAtivas()
                                    ->ativas()
                                    ->where('assembleia_id', $assembleiaId)
                                    ->pluck('nome_completo', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->minItems(1)
                            ->helperText('Apenas meninas ativas da assembleia selecionada são elegíveis')
                            ->hintAction(
                                Action::make('refresh_members')
                                    ->label('Atualizar Lista')
                                    ->icon('heroicon-m-arrow-path')
                                    ->action(function (Set $set, Get $get) {
                                        $set('membros_selecionados', []);
                                    })
                            ),
                            

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
        $lastProtocol = \App\Models\Protocolo::whereYear('created_at', $year)->count();
        $number = str_pad($lastProtocol + 1, 3, '0', STR_PAD_LEFT);
        
        return "PR-MAI-{$year}-{$number}";
    }
}