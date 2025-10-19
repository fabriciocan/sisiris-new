<?php

namespace App\Filament\Resources\Protocolos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProtocoloForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                        // Se for admin_assembleia, pré-seleciona sua assembleia
                        if ($user && $user->hasRole('admin_assembleia') && $user->membro) {
                            return $user->membro->assembleia_id;
                        }
                        return null;
                    })
                    ->disabled(function () {
                        /** @var User|null $user */
                        $user = Auth::user();
                        // Apenas admin_assembleia tem campo bloqueado
                        // Admin jurisdição pode alterar assembleia
                        return $user && $user->hasRole('admin_assembleia');
                    })
                    ->dehydrated() // Garante que o valor será salvo mesmo desabilitado
                    ->helperText(function () {
                        /** @var User|null $user */
                        $user = Auth::user();
                        if ($user && $user->hasRole('admin_assembleia')) {
                            return 'Assembleia definida automaticamente baseada no seu perfil';
                        }
                        if ($user && $user->hasRole('membro_jurisdicao')) {
                            return 'Como admin da jurisdição, você pode selecionar qualquer assembleia';
                        }
                        return 'Selecione a assembleia responsável';
                    }),
                    
                Select::make('tipo')
                    ->label('Tipo de Protocolo')
                    ->options([
                        'iniciacao' => 'Iniciação',
                        'transferencia' => 'Transferência',
                        'afastamento' => 'Afastamento',
                        'retorno' => 'Retorno',
                        'maioridade' => 'Maioridade',
                        'desligamento' => 'Desligamento',
                        'premios_honrarias' => 'Prêmios/Honrarias',
                    ])
                    ->required(),
                    


                Select::make('membro_id')
                    ->label('Membro Relacionado')
                    ->relationship('membro', 'nome_completo')
                    ->searchable()
                    ->preload()
                    ->helperText('Membro ao qual o protocolo se refere'),
                    
                Hidden::make('solicitante_id')
                    ->default(fn () => Auth::id()),
                    

                    
                DatePicker::make('data_solicitacao')
                    ->label('Data de Solicitação')
                    ->default(now())
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                Hidden::make('status')
                    ->default('em_analise'),
                    
                Hidden::make('etapa_atual')
                    ->default('aprovacao'),
                    

                    

            ])
            ->columns(2);
    }

    protected static function generateProtocolNumber(): string
    {
        $year = date('Y');
        $lastProtocol = \App\Models\Protocolo::whereYear('created_at', $year)->count();
        $number = str_pad($lastProtocol + 1, 3, '0', STR_PAD_LEFT);
        
        return "PR-{$year}-{$number}";
    }

    protected static function generateTitleForType(?string $type): ?string
    {
        return match ($type) {
            'iniciacao' => 'Solicitação de Iniciação',
            'transferencia' => 'Solicitação de Transferência',
            'afastamento' => 'Solicitação de Afastamento',
            'retorno' => 'Solicitação de Retorno',
            'maioridade' => 'Declaração de Maioridade',
            'desligamento' => 'Solicitação de Desligamento',
            'premios_honrarias' => 'Solicitação de Prêmios/Honrarias',
            default => null,
        };
    }


}
