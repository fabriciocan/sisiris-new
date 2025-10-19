<?php

namespace App\Filament\Resources\Tickets\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;

class TicketRespostasRelationManager extends RelationManager
{
    protected static string $relationship = 'ticketRespostas';

    protected static ?string $recordTitleAttribute = 'numero';

    protected static ?string $title = 'Respostas';

    protected static ?string $label = 'Resposta';

    protected static ?string $pluralLabel = 'Respostas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('resposta')
                    ->label('Resposta')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                Toggle::make('e_interna')
                    ->label('Resposta Interna')
                    ->helperText('Marque se esta resposta é apenas para visualização interna da equipe')
                    ->default(false),

                Select::make('mencionados')
                    ->label('Mencionar Usuários')
                    ->multiple()
                    ->relationship('mencionados', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Selecione usuários para notificar sobre esta resposta'),

                FileUpload::make('anexos')
                    ->label('Anexos')
                    ->multiple()
                    ->directory('ticket-respostas')
                    ->acceptedFileTypes(['image/*', 'application/pdf', '.doc', '.docx', '.xlsx', '.txt'])
                    ->maxSize(10240) // 10MB
                    ->columnSpanFull(),

                Hidden::make('usuario_id')
                    ->default(Auth::id()),

                Hidden::make('numero')
                    ->default(fn() => $this->generateNumeroResposta()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero')
            ->columns([
                TextColumn::make('numero')
                    ->label('#')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('usuario.name')
                    ->label('Usuário')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('resposta')
                    ->label('Resposta')
                    ->limit(100)
                    ->wrap()
                    ->html(),

                IconColumn::make('e_interna')
                    ->label('Interna')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('heroicon-o-eye')
                    ->tooltip(fn($record) => $record->e_interna ? 'Resposta interna' : 'Resposta pública'),

                TextColumn::make('anexos_count')
                    ->label('Anexos')
                    ->counts('anexos')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('apenas_publicas')
                    ->label('Apenas Públicas')
                    ->query(fn (Builder $query): Builder => $query->where('e_interna', false)),
                    
                Filter::make('apenas_internas')
                    ->label('Apenas Internas')
                    ->query(fn (Builder $query): Builder => $query->where('e_interna', true)),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn($record) => $record->usuario_id === Auth::id()),
                    
                DeleteAction::make()
                    ->visible(fn($record) => $record->usuario_id === Auth::id()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'asc')
            ->poll('30s'); // Atualiza automaticamente a cada 30 segundos
    }

    protected function generateNumeroResposta(): string
    {
        $ano = now()->year;
        $proximoNumero = \App\Models\TicketResposta::whereYear('created_at', $ano)->count() + 1;
        
        return sprintf('RSP-%d-%04d', $ano, $proximoNumero);
    }

    public function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Por enquanto, mostrar todas as respostas
        // Implementar lógica de permissão depois
        
        return $query;
    }
}