<?php

namespace App\Filament\Resources\Protocolos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Membro;
use Illuminate\Support\Facades\Auth;

class AfastamentoFormSchema extends BaseProtocoloSchema
{
    /**
     * Configura o schema do formulário de Afastamento
     */
    public static function make(): array
    {
        $user = Auth::user();
        $isMembroJurisdicao = $user?->hasRole('membro_jurisdicao');

        return [
            // Seção: Informações Básicas
            Section::make('Informações do Protocolo')
                ->description('Dados do protocolo de afastamento')
                ->schema([
                    Grid::make(2)->schema([
                        // Assembleia (apenas para membro jurisdição)
                        $isMembroJurisdicao
                            ? static::assembleiaSelect()->live()
                            : static::assembleiaHidden(),

                        // Tipo de protocolo (readonly)
                        static::tipoProtocoloField()
                            ->default('afastamento'),
                    ]),

                    // Título automático
                    static::tituloField()
                        ->default('Protocolo de Afastamento')
                        ->disabled()
                        ->dehydrated(),
                ])
                ->collapsible(),

            // Seção: Seleção de Membro
            Section::make('Membro a Afastar')
                ->description('Selecione o membro que será afastado')
                ->schema([
                    Grid::make(2)->schema([
                        // Select de membro ativo
                        Select::make('membro_id')
                            ->label('Membro')
                            ->options(function ($get) use ($user, $isMembroJurisdicao) {
                                $query = Membro::where('status', 'ativa');

                                // Para membro jurisdição, filtrar pela assembleia selecionada
                                if ($isMembroJurisdicao) {
                                    $assembleiaId = $get('assembleia_id');
                                    if ($assembleiaId) {
                                        $query->where('assembleia_id', $assembleiaId);
                                    }
                                }
                                // Para admin assembleia, filtrar pela assembleia do usuário
                                elseif ($user?->membro?->assembleia_id) {
                                    $query->where('assembleia_id', $user->membro->assembleia_id);
                                }

                                return $query->orderBy('nome_completo')
                                    ->pluck('nome_completo', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->helperText($isMembroJurisdicao
                                ? 'Selecione primeiro a assembleia acima'
                                : 'Apenas membros ativos podem ser afastados')
                            ->placeholder('Selecione o membro...'),

                        // Data do afastamento
                        DatePicker::make('data_afastamento')
                            ->label('Data do Afastamento')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required()
                            ->maxDate(now())
                            ->helperText('Data em que o afastamento ocorreu')
                            ->placeholder('Selecione a data...'),
                    ]),

                    // Motivo do afastamento
                    Textarea::make('motivo_afastamento')
                        ->label('Motivo do Afastamento')
                        ->rows(4)
                        ->required()
                        ->maxLength(1000)
                        ->placeholder('Descreva o motivo do afastamento...')
                        ->helperText('Este motivo ficará registrado no histórico do membro'),

                    // Observações adicionais
                    static::observacoesField()
                        ->helperText('Informações complementares (opcional)'),
                ])
                ->collapsible(),
        ];
    }

    /**
     * Schema para aprovação do protocolo de Afastamento
     */
    public static function makeApprovalSchema(): array
    {
        return [
            // Seção: Informações do Protocolo (readonly)
            Section::make('Dados do Protocolo')
                ->description('Informações do protocolo de afastamento')
                ->schema([
                    Grid::make(3)->schema([
                        static::tipoProtocoloField(),

                        \Filament\Forms\Components\TextInput::make('numero_protocolo')
                            ->label('Número do Protocolo')
                            ->disabled(),

                        \Filament\Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                    ]),
                ])
                ->collapsible()
                ->collapsed(),

            // Seção: Dados do Membro
            Section::make('Membro a ser Afastado')
                ->description('Informações do membro que será afastado')
                ->schema([
                    Grid::make(2)->schema([
                        \Filament\Forms\Components\TextInput::make('membro.nome_completo')
                            ->label('Nome Completo')
                            ->disabled(),

                        \Filament\Forms\Components\TextInput::make('membro.numero_membro')
                            ->label('Número de Membro')
                            ->disabled(),
                    ]),

                    Grid::make(3)->schema([
                        \Filament\Forms\Components\TextInput::make('membro.status')
                            ->label('Status Atual')
                            ->disabled(),

                        \Filament\Forms\Components\TextInput::make('data_afastamento')
                            ->label('Data do Afastamento')
                            ->disabled(),

                        \Filament\Forms\Components\TextInput::make('membro.data_iniciacao')
                            ->label('Data de Iniciação')
                            ->disabled(),
                    ]),

                    Textarea::make('motivo_afastamento')
                        ->label('Motivo do Afastamento')
                        ->disabled()
                        ->rows(3),

                    Textarea::make('observacoes')
                        ->label('Observações')
                        ->disabled()
                        ->rows(2)
                        ->visible(fn ($record) => !empty($record?->observacoes)),
                ])
                ->collapsible(),

            // Seção: Aprovação/Rejeição
            Section::make('Decisão')
                ->description('Aprovar ou rejeitar o protocolo de afastamento')
                ->schema([
                    Select::make('decisao')
                        ->label('Decisão')
                        ->options([
                            'aprovar' => 'Aprovar Afastamento',
                            'rejeitar' => 'Rejeitar Protocolo',
                        ])
                        ->required()
                        ->live()
                        ->placeholder('Selecione uma opção...'),

                    // Feedback (apenas se rejeitar)
                    static::feedbackRejeicaoField()
                        ->visible(fn ($get) => $get('decisao') === 'rejeitar')
                        ->required(fn ($get) => $get('decisao') === 'rejeitar'),

                    // Observações da aprovação
                    Textarea::make('observacoes_aprovacao')
                        ->label('Observações da Aprovação')
                        ->rows(3)
                        ->visible(fn ($get) => $get('decisao') === 'aprovar')
                        ->placeholder('Observações sobre a aprovação (opcional)...')
                        ->helperText('Estas observações serão adicionadas ao histórico'),
                ])
                ->collapsible(),
        ];
    }
}
