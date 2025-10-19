<?php

namespace App\Filament\Resources\Comissoes\RelationManagers;

use App\Models\Membro;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class MembrosRelationManager extends RelationManager
{
    protected static string $relationship = 'comissaoMembros';

    protected static ?string $recordTitleAttribute = 'membro.nome_completo';

    protected static ?string $title = 'Membros da Comissão';

    protected static ?string $label = 'Membro';

    protected static ?string $pluralLabel = 'Membros';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('membro_id')
                    ->label('Membro')
                    ->relationship('membro', 'nome_completo')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn(Membro $record): string => "{$record->nome_completo} - {$record->assembleia->nome}"),

                Select::make('cargo')
                    ->label('Cargo')
                    ->options([
                        'presidente' => 'Presidente',
                        'vice_presidente' => 'Vice-Presidente',
                        'secretario' => 'Secretário',
                        'tesoureiro' => 'Tesoureiro',
                        'membro' => 'Membro',
                        'coordenador' => 'Coordenador',
                    ])
                    ->required()
                    ->default('membro'),

                DatePicker::make('data_inicio')
                    ->label('Data de Início')
                    ->required()
                    ->default(now()),

                DatePicker::make('data_fim')
                    ->label('Data de Fim')
                    ->nullable()
                    ->after('data_inicio'),

                Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true),

                TextInput::make('observacoes')
                    ->label('Observações')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('membro.nome_completo')
            ->columns([
                TextColumn::make('membro.nome_completo')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('membro.assembleia.nome')
                    ->label('Assembleia')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('cargo')
                    ->label('Cargo')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'presidente' => 'Presidente',
                        'vice_presidente' => 'Vice-Presidente',
                        'secretario' => 'Secretário',
                        'tesoureiro' => 'Tesoureiro',
                        'membro' => 'Membro',
                        'coordenador' => 'Coordenador',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'presidente' => 'danger',
                        'vice_presidente' => 'warning',
                        'secretario' => 'info',
                        'tesoureiro' => 'success',
                        'coordenador' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('data_inicio')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('data_fim')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Em exercício'),

                IconColumn::make('ativo')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Ações serão adicionadas posteriormente
            ])
            ->actions([
                // Ações serão adicionadas posteriormente
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('data_inicio', 'desc')
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['membro.assembleia']));
    }
}
