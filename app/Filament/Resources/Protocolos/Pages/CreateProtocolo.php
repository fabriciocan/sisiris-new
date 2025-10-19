<?php

namespace App\Filament\Resources\Protocolos\Pages;

use App\Filament\Resources\Protocolos\ProtocoloResource;
use App\Filament\Resources\Protocolos\Schemas\IniciacaoProtocoloForm;
use App\Filament\Resources\Protocolos\Schemas\MaioridadeProtocoloForm;
use App\Models\Protocolo;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateProtocolo extends CreateRecord
{
    protected static string $resource = ProtocoloResource::class;

    protected static ?string $title = 'Criar Novo Protocolo';

    public function getHeading(): string
    {
        return 'Criar Novo Protocolo';
    }

    public function getSubheading(): ?string
    {
        return 'Selecione o tipo de protocolo e preencha as informações necessárias';
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Tipo de Protocolo')
                ->description('Selecione o tipo de protocolo que deseja criar')
                ->schema([
                    Select::make('tipo_protocolo')
                        ->label('Tipo de Protocolo')
                        ->options([
                            'iniciacao' => 'Iniciação - Protocolo para iniciação de novas meninas',
                            'maioridade' => 'Maioridade - Protocolo para passagem à maioridade',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, $state) {
                            // Limpar dados quando mudar o tipo
                            $set('novas_meninas', []);
                            $set('membros_selecionados', []);
                        })
                        ->placeholder('Selecione o tipo de protocolo...')
                        ->helperText('Escolha o tipo de protocolo que deseja criar'),
                        
                    Placeholder::make('tipo_info')
                        ->label('')
                        ->content(function (Get $get): string {
                            return match ($get('tipo_protocolo')) {
                                'iniciacao' => '📝 Você está criando um protocolo de iniciação para cadastrar novas meninas na assembleia.',
                                'maioridade' => '🎓 Você está criando um protocolo de maioridade para meninas ativas que completaram os requisitos.',
                                default => '👆 Selecione um tipo de protocolo acima para continuar.',
                            };
                        })
                        ->visible(fn (Get $get): bool => filled($get('tipo_protocolo'))),
                ])
                ->columnSpanFull(),

            // Formulário dinâmico baseado no tipo selecionado
            Section::make('Dados do Protocolo')
                ->schema(function (Get $get): array {
                    $tipo = $get('tipo_protocolo');
                    
                    if ($tipo === 'iniciacao') {
                        return $this->getIniciacaoFormComponents();
                    } elseif ($tipo === 'maioridade') {
                        return $this->getMaioridadeFormComponents();
                    }
                    
                    return [
                        Placeholder::make('select_type_first')
                            ->label('')
                            ->content('Selecione um tipo de protocolo acima para ver o formulário.')
                    ];
                })
                ->visible(fn (Get $get): bool => filled($get('tipo_protocolo')))
                ->columnSpanFull(),
        ];
    }

    protected function getIniciacaoFormComponents(): array
    {
        // Usar os componentes do IniciacaoProtocoloForm, mas sem a seção de tipo
        $schema = IniciacaoProtocoloForm::configure($this->makeSchema());
        $components = $schema->getComponents();
        
        // Remover a primeira seção (Informações do Protocolo) e pegar apenas os campos necessários
        $filteredComponents = [];
        
        foreach ($components as $component) {
            if ($component instanceof \Filament\Forms\Components\Section) {
                $label = $component->getLabel();
                // Pular a seção de informações básicas, manter apenas as seções específicas
                if ($label !== 'Informações do Protocolo') {
                    $filteredComponents[] = $component;
                }
            }
        }
        
        return $filteredComponents;
    }

    protected function getMaioridadeFormComponents(): array
    {
        // Usar os componentes do MaioridadeProtocoloForm, mas sem a seção de tipo
        $schema = MaioridadeProtocoloForm::configure($this->makeSchema());
        $components = $schema->getComponents();
        
        // Remover a primeira seção (Informações do Protocolo) e pegar apenas os campos necessários
        $filteredComponents = [];
        
        foreach ($components as $component) {
            if ($component instanceof \Filament\Forms\Components\Section) {
                $label = $component->getLabel();
                // Pular a seção de informações básicas, manter apenas as seções específicas
                if ($label !== 'Informações do Protocolo') {
                    $filteredComponents[] = $component;
                }
            }
        }
        
        return $filteredComponents;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Set default values
        $data['solicitante_id'] = $user->id;
        $data['status'] = 'em_analise';
        $data['etapa_atual'] = 'aprovacao';
        $data['data_solicitacao'] = now();

        // Auto-select assembleia for admin_assembleia
        if ($user->hasRole('admin_assembleia') && $user->membro) {
            $data['assembleia_id'] = $user->membro->assembleia_id;
        }

        // Generate protocol number based on type
        $data['numero_protocolo'] = $this->generateProtocolNumber($data['tipo_protocolo']);

        // Handle specific data based on protocol type
        if ($data['tipo_protocolo'] === 'iniciacao' && isset($data['novas_meninas'])) {
            $data['dados_membros'] = $data['novas_meninas'];
            unset($data['novas_meninas']);
        } elseif ($data['tipo_protocolo'] === 'maioridade' && isset($data['membros_selecionados'])) {
            $data['dados_membros'] = $data['membros_selecionados'];
            unset($data['membros_selecionados']);
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Create the protocol
            $protocolo = Protocolo::create($data);

            // Initialize workflow
            $protocolo->initializeWorkflow();

            return $protocolo;
        });
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Protocolo Criado')
            ->body('O protocolo foi criado com sucesso e enviado para aprovação.')
            ->duration(5000);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function authorizeAccess(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('admin_assembleia')) {
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    public function mount(): void
    {
        $this->authorizeAccess();
        parent::mount();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Criar Protocolo')
                ->icon('heroicon-o-plus-circle'),
            $this->getCancelFormAction()
                ->label('Cancelar')
                ->color('gray'),
        ];
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return Actions\Action::make('cancel')
            ->label('Cancelar')
            ->color('gray')
            ->url($this->getResource()::getUrl('index'));
    }

    protected function generateProtocolNumber(string $tipo): string
    {
        $year = date('Y');
        $prefix = match ($tipo) {
            'iniciacao' => 'INI',
            'maioridade' => 'MAI',
            default => 'GEN',
        };
        
        $lastProtocol = Protocolo::whereYear('created_at', $year)
            ->where('tipo_protocolo', $tipo)
            ->count();
        $number = str_pad($lastProtocol + 1, 3, '0', STR_PAD_LEFT);
        
        return "PR-{$prefix}-{$year}-{$number}";
    }
}