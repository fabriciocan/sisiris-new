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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AnexosRelationManager extends RelationManager
{
    protected static string $relationship = 'anexos';

    protected static ?string $recordTitleAttribute = 'nome_arquivo';

    protected static ?string $title = 'Anexos';

    protected static ?string $label = 'Anexo';

    protected static ?string $pluralLabel = 'Anexos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('caminho_arquivo')
                    ->label('Arquivo')
                    ->required()
                    ->disk('public')
                    ->directory('protocolos/anexos')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(10240) // 10MB
                    ->helperText('Tipos permitidos: PDF, Imagens, DOC, DOCX. Tamanho máximo: 10MB')
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $filename = is_array($state) ? $state[0] : $state;
                            $set('nome_arquivo', basename($filename));
                            
                            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $mimeTypes = [
                                'pdf' => 'application/pdf',
                                'jpg' => 'image/jpeg',
                                'jpeg' => 'image/jpeg',
                                'png' => 'image/png',
                                'gif' => 'image/gif',
                                'doc' => 'application/msword',
                                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ];
                            $set('tipo_arquivo', $mimeTypes[$extension] ?? 'application/octet-stream');
                        }
                    }),

                TextInput::make('nome_arquivo')
                    ->label('Nome do Arquivo')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Nome será preenchido automaticamente baseado no arquivo enviado'),

                Hidden::make('tipo_arquivo'),
                Hidden::make('tamanho'),
                Hidden::make('uploaded_by')
                    ->default(Auth::id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nome_arquivo')
            ->columns([
                TextColumn::make('nome_arquivo')
                    ->label('Nome do Arquivo')
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tipo_arquivo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'pdf') => 'danger',
                        str_contains($state, 'image') => 'success',
                        str_contains($state, 'word') || str_contains($state, 'document') => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match (true) {
                        str_contains($state, 'pdf') => 'PDF',
                        str_contains($state, 'image') => 'Imagem',
                        str_contains($state, 'word') || str_contains($state, 'document') => 'Word',
                        default => 'Arquivo',
                    }),

                TextColumn::make('tamanho')
                    ->label('Tamanho')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $this->formatBytes($state)),

                TextColumn::make('uploadedBy.name')
                    ->label('Enviado por')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data de Upload')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo_arquivo')
                    ->label('Tipo de Arquivo')
                    ->options([
                        'application/pdf' => 'PDF',
                        'image/jpeg' => 'Imagem JPEG',
                        'image/png' => 'Imagem PNG',
                        'application/msword' => 'Word (.doc)',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word (.docx)',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Anexar Arquivo')
                    ->icon('heroicon-o-paper-clip')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (isset($data['caminho_arquivo']) && Storage::disk('public')->exists($data['caminho_arquivo'])) {
                            $data['tamanho'] = Storage::disk('public')->size($data['caminho_arquivo']);
                        }
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil'),

                DeleteAction::make()
                    ->label('Excluir')
                    ->icon('heroicon-o-trash')
                    ->before(function ($record) {
                        if ($record->caminho_arquivo && Storage::disk('public')->exists($record->caminho_arquivo)) {
                            Storage::disk('public')->delete($record->caminho_arquivo);
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->caminho_arquivo && Storage::disk('public')->exists($record->caminho_arquivo)) {
                                    Storage::disk('public')->delete($record->caminho_arquivo);
                                }
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Anexar Primeiro Arquivo')
                    ->icon('heroicon-o-paper-clip'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private function formatBytes($size, $precision = 2)
    {
        if ($size > 0) {
            $base = log($size, 1024);
            $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
            return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
        }
        return $size;
    }
}