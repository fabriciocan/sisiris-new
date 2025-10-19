<?php

namespace App\Filament\Resources\Protocolos\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class TaxasRelationManager extends RelationManager
{
    protected static string $relationship = 'taxas';

    protected static ?string $recordTitleAttribute = 'descricao';

    protected static ?string $title = 'Taxas';

    protected static ?string $label = 'Taxa';

    protected static ?string $pluralLabel = 'Taxas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex: Taxa de protocolo, Taxa de urgência, etc.'),

                TextInput::make('valor')
                    ->label('Valor (R$)')
                    ->required()
                    ->numeric()
                    ->prefix('R$')
                    ->placeholder('0,00')
                    ->minValue(0)
                    ->step(0.01),

                Toggle::make('pago')
                    ->label('Pago')
                    ->default(false)
                    ->live()
                    ->helperText('Marque como pago quando o pagamento for confirmado'),

                DatePicker::make('data_pagamento')
                    ->label('Data do Pagamento')
                    ->visible(fn ($get) => $get('pago'))
                    ->required(fn ($get) => $get('pago'))
                    ->format('d/m/Y')
                    ->displayFormat('d/m/Y')
                    ->helperText('Data em que o pagamento foi realizado'),

                Select::make('forma_pagamento')
                    ->label('Forma de Pagamento')
                    ->visible(fn ($get) => $get('pago'))
                    ->required(fn ($get) => $get('pago'))
                    ->options([
                        'dinheiro' => 'Dinheiro',
                        'cartao_credito' => 'Cartão de Crédito',
                        'cartao_debito' => 'Cartão de Débito',
                        'transferencia' => 'Transferência',
                        'pix' => 'PIX',
                        'cheque' => 'Cheque',
                        'outros' => 'Outros',
                    ])
                    ->placeholder('Selecione a forma de pagamento'),

                FileUpload::make('comprovante')
                    ->label('Comprovante de Pagamento')
                    ->visible(fn ($get) => $get('pago'))
                    ->disk('public')
                    ->directory('protocolos/comprovantes')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(5120) // 5MB
                    ->helperText('Anexe o comprovante de pagamento (PDF ou imagem, máx. 5MB)'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descricao')
            ->columns([
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->color('success'),

                IconColumn::make('pago')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('data_pagamento')
                    ->label('Data Pagamento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—')
                    ->color('info'),

                TextColumn::make('forma_pagamento')
                    ->label('Forma Pagamento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pix' => 'success',
                        'cartao_credito', 'cartao_debito' => 'info',
                        'transferencia' => 'warning',
                        'dinheiro' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cartao_credito' => 'Cartão Crédito',
                        'cartao_debito' => 'Cartão Débito',
                        'transferencia' => 'Transferência',
                        'pix' => 'PIX',
                        'dinheiro' => 'Dinheiro',
                        'cheque' => 'Cheque',
                        'outros' => 'Outros',
                        default => $state,
                    })
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('pago')
                    ->label('Status do Pagamento')
                    ->placeholder('Todos')
                    ->trueLabel('Pago')
                    ->falseLabel('Pendente'),

                SelectFilter::make('forma_pagamento')
                    ->label('Forma de Pagamento')
                    ->options([
                        'dinheiro' => 'Dinheiro',
                        'cartao_credito' => 'Cartão de Crédito',
                        'cartao_debito' => 'Cartão de Débito',
                        'transferencia' => 'Transferência',
                        'pix' => 'PIX',
                        'cheque' => 'Cheque',
                        'outros' => 'Outros',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nova Taxa')
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil'),

                DeleteAction::make()
                    ->label('Excluir')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Adicionar Primeira Taxa')
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Calcular total de taxas
     */
    public function getTotalTaxas(): array
    {
        $taxas = $this->getOwnerRecord()->taxas;
        
        return [
            'total' => $taxas->sum('valor'),
            'pagas' => $taxas->where('pago', true)->sum('valor'),
            'pendentes' => $taxas->where('pago', false)->sum('valor'),
        ];
    }
}