<?php

namespace App\Filament\Resources\Protocolos\Schemas;

use App\Models\Assembleia;
use App\Models\Membro;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;

/**
 * Classe base com componentes reutilizáveis para formulários de protocolos
 */
class BaseProtocoloSchema
{
    /**
     * Campo select de assembleia (apenas para membro jurisdição)
     */
    public static function assembleiaSelect(): Select
    {
        return Select::make('assembleia_id')
            ->label('Assembleia')
            ->options(Assembleia::query()->pluck('nome', 'id'))
            ->required()
            ->searchable()
            ->preload()
            ->visible(fn() => Auth::user()?->hasRole('membro_jurisdicao'))
            ->default(function () {
                $user = Auth::user();
                if ($user && $user->hasRole('admin_assembleia')) {
                    return $user->membro?->assembleia_id;
                }
                return null;
            });
    }

    /**
     * Campo hidden de assembleia (para admin assembleia)
     */
    public static function assembleiaHidden(): \Filament\Forms\Components\Hidden
    {
        return \Filament\Forms\Components\Hidden::make('assembleia_id')
            ->default(fn() => Auth::user()?->membro?->assembleia_id)
            ->required();
    }

    /**
     * Campo de tipo de protocolo
     */
    public static function tipoProtocoloField(): Select
    {
        return Select::make('tipo_protocolo')
            ->label('Tipo de Protocolo')
            ->options([
                'maioridade' => 'Cerimônia de Maioridade',
                'iniciacao' => 'Iniciação',
                'homenageados_ano' => 'Homenageados do Ano',
                'coracao_cores' => 'Coração das Cores',
                'grande_cruz_cores' => 'Grande Cruz das Cores',
                'afastamento' => 'Afastamento',
                'novos_cargos_assembleia' => 'Novos Cargos - Assembleia',
                'novos_cargos_conselho' => 'Novos Cargos - Conselho',
            ])
            ->required()
            ->disabled();
    }

    /**
     * Campo de título
     */
    public static function tituloField(): TextInput
    {
        return TextInput::make('titulo')
            ->label('Título')
            ->maxLength(255)
            ->placeholder('Título do protocolo...');
    }

    /**
     * Campo de descrição
     */
    public static function descricaoField(): Textarea
    {
        return Textarea::make('descricao')
            ->label('Descrição')
            ->rows(3)
            ->maxLength(1000)
            ->placeholder('Descrição adicional...');
    }

    /**
     * Campo de observações
     */
    public static function observacoesField(): Textarea
    {
        return Textarea::make('observacoes')
            ->label('Observações')
            ->rows(3)
            ->maxLength(1000)
            ->placeholder('Observações adicionais...');
    }

    /**
     * Campo de data da cerimônia
     */
    public static function dataCerimoniaField(bool $required = false): DatePicker
    {
        return DatePicker::make('data_cerimonia')
            ->label('Data da Cerimônia')
            ->native(false)
            ->displayFormat('d/m/Y')
            ->required($required)
            ->placeholder('Selecione a data...');
    }

    /**
     * Campo de valor da taxa
     */
    public static function valorTaxaField(bool $required = false): TextInput
    {
        return TextInput::make('valor_taxa')
            ->label('Valor da Taxa')
            ->prefix('R$')
            ->numeric()
            ->minValue(0)
            ->step(0.01)
            ->required($required)
            ->placeholder('0,00');
    }

    /**
     * Campo de comprovante de pagamento
     */
    public static function comprovantePagamentoField(bool $required = false): FileUpload
    {
        return FileUpload::make('comprovante_pagamento')
            ->label('Comprovante de Pagamento')
            ->acceptedFileTypes(['application/pdf', 'image/*'])
            ->maxSize(5120) // 5MB
            ->directory('protocolos/comprovantes')
            ->visibility('private')
            ->required($required)
            ->helperText('Formatos aceitos: PDF ou imagens (máx. 5MB)');
    }

    /**
     * Campo de feedback de rejeição
     */
    public static function feedbackRejeicaoField(bool $required = false): Textarea
    {
        return Textarea::make('feedback_rejeicao')
            ->label('Motivo da Rejeição')
            ->rows(4)
            ->required($required)
            ->placeholder('Descreva o motivo da rejeição...')
            ->helperText('Este feedback será visível para o admin da assembleia');
    }

    /**
     * Select de meninas ativas da assembleia
     */
    public static function meninasAtivasSelect(
        ?int $assembleiaId = null,
        bool $multiple = true
    ): Select {
        return Select::make('membros')
            ->label('Meninas Ativas')
            ->options(function () use ($assembleiaId) {
                $query = Membro::meninasAtivas()->ativos();

                if ($assembleiaId) {
                    $query->where('assembleia_id', $assembleiaId);
                } elseif (Auth::user()?->membro?->assembleia_id) {
                    $query->where('assembleia_id', Auth::user()->membro->assembleia_id);
                }

                return $query->pluck('nome_completo', 'id');
            })
            ->multiple($multiple)
            ->searchable()
            ->preload()
            ->required()
            ->helperText('Selecione as meninas que participarão');
    }

    /**
     * Select de membros elegíveis para honraria específica
     */
    public static function membrosElegiveisHonraria(
        string $tipoHonraria,
        ?int $assembleiaId = null
    ): Select {
        return Select::make('membros')
            ->label('Membros')
            ->options(function () use ($tipoHonraria, $assembleiaId) {
                $query = Membro::query()
                    ->where('status', 'ativa')
                    ->whereDoesntHave('honrarias', function ($q) use ($tipoHonraria) {
                        $q->where('tipo_honraria', $tipoHonraria);
                    });

                if ($assembleiaId) {
                    $query->where('assembleia_id', $assembleiaId);
                } elseif (Auth::user()?->membro?->assembleia_id) {
                    $query->where('assembleia_id', Auth::user()->membro->assembleia_id);
                }

                return $query->pluck('nome_completo', 'id');
            })
            ->multiple()
            ->searchable()
            ->preload()
            ->required()
            ->helperText('Apenas membros que ainda não receberam esta honraria');
    }

    /**
     * Select de membros ativos da assembleia
     */
    public static function membrosAtivosSelect(
        ?int $assembleiaId = null,
        bool $multiple = false
    ): Select {
        return Select::make('membro_id')
            ->label('Membro')
            ->options(function () use ($assembleiaId) {
                $query = Membro::where('status', 'ativa');

                if ($assembleiaId) {
                    $query->where('assembleia_id', $assembleiaId);
                } elseif (Auth::user()?->membro?->assembleia_id) {
                    $query->where('assembleia_id', Auth::user()->membro->assembleia_id);
                }

                return $query->pluck('nome_completo', 'id');
            })
            ->multiple($multiple)
            ->searchable()
            ->preload()
            ->required();
    }

    /**
     * Section de informações do protocolo
     */
    public static function infoSection(): Section
    {
        return Section::make('Informações do Protocolo')
            ->description('Dados básicos do protocolo')
            ->schema([
                Grid::make(2)->schema([
                    static::tituloField(),
                    static::tipoProtocoloField(),
                ]),
                static::descricaoField(),
            ]);
    }

    /**
     * Section de feedback (para rejeições)
     */
    public static function feedbackSection(): Section
    {
        return Section::make('Feedback')
            ->description('Informações sobre a rejeição')
            ->schema([
                static::feedbackRejeicaoField(true),
            ])
            ->visible(fn($record) => $record && $record->status === 'rejeitado');
    }

    /**
     * Section de pagamento
     */
    public static function pagamentoSection(bool $showTaxa = true): Section
    {
        $schema = [];

        if ($showTaxa) {
            $schema[] = static::valorTaxaField(true);
        }

        $schema[] = static::comprovantePagamentoField(true);

        return Section::make('Pagamento')
            ->description('Informações de taxa e comprovante')
            ->schema($schema);
    }

    /**
     * Checkbox de confirmação
     */
    public static function confirmacaoCheckbox(string $label): Checkbox
    {
        return Checkbox::make('confirmacao')
            ->label($label)
            ->required()
            ->accepted();
    }
}
