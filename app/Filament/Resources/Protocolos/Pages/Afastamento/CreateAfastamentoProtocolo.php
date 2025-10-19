<?php

namespace App\Filament\Resources\Protocolos\Pages\Afastamento;

use App\Filament\Resources\Protocolos\Schemas\AfastamentoFormSchema;
use App\Helpers\ProtocoloLogger;
use App\Models\Protocolo;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class CreateAfastamentoProtocolo extends CreateRecord
{
    protected static string $resource = \App\Filament\Resources\Protocolos\ProtocoloResource::class;

    public function getHeading(): string
    {
        return 'Novo Protocolo de Afastamento';
    }

    public function getSubheading(): ?string
    {
        return 'Registre o afastamento de um membro da assembleia';
    }

    public function mount(): void
    {
        // Verificar permissão
        $user = Auth::user();
        if (!$user || (!$user->hasRole('admin_assembleia') && !$user->hasRole('membro_jurisdicao'))) {
            Notification::make()
                ->danger()
                ->title('Acesso Negado')
                ->body('Você não tem permissão para criar protocolos de afastamento.')
                ->send();

            $this->redirect(route('filament.admin.resources.protocolos.index'));
            return;
        }

        parent::mount();
    }

    protected function getFormSchema(): array
    {
        return AfastamentoFormSchema::make();
    }

    /**
     * Mutate form data before creating the record
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Definir assembleia_id
        if (!isset($data['assembleia_id'])) {
            // Para admin_assembleia, pegar a assembleia do membro vinculado
            if ($user->membro && $user->membro->assembleia_id) {
                $data['assembleia_id'] = $user->membro->assembleia_id;
            } else {
                // Isso não deveria acontecer, mas como fallback, lançar erro
                throw new \Exception('Assembleia não especificada. Por favor, selecione uma assembleia.');
            }
        }

        // Preparar dados do protocolo
        $data['numero_protocolo'] = $this->gerarNumeroProtocolo($data['assembleia_id']);
        $data['tipo_protocolo'] = 'afastamento';
        $data['titulo'] = $data['titulo'] ?? 'Protocolo de Afastamento';
        $data['descricao'] = "Afastamento do membro";
        $data['solicitante_id'] = $user->id;
        $data['status'] = 'pendente';
        $data['data_solicitacao'] = now();
        $data['dados_json'] = [
            'data_afastamento' => $data['data_afastamento'] ?? null,
            'motivo_afastamento' => $data['motivo_afastamento'] ?? null,
        ];

        return $data;
    }

    /**
     * Handle the record creation
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Criar protocolo
        $protocolo = Protocolo::create($data);

        // Inicializar workflow
        $protocolo->initializeWorkflow();

        // Log adicional com detalhes
        ProtocoloLogger::log(
            $protocolo,
            'criacao_detalhada',
            "Protocolo de afastamento criado para o membro",
            null,
            [
                'membro_id' => $data['membro_id'] ?? null,
                'data_afastamento' => $data['dados_json']['data_afastamento'] ?? null,
                'motivo' => $data['dados_json']['motivo_afastamento'] ?? null,
            ],
            null,
            Auth::user()
        );

        return $protocolo;
    }

    /**
     * Get the success notification
     */
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Protocolo Criado!')
            ->body("Protocolo {$this->getRecord()->numero_protocolo} criado com sucesso.");
    }

    /**
     * Get the redirect URL after creating
     */
    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.protocolos.index');
    }

    /**
     * Gera número de protocolo único
     */
    protected function gerarNumeroProtocolo(int $assembleiaId): string
    {
        $ano = now()->year;
        $ultimoProtocolo = Protocolo::where('assembleia_id', $assembleiaId)
            ->where('numero_protocolo', 'like', "AFST-{$ano}-%")
            ->orderBy('numero_protocolo', 'desc')
            ->first();

        if (!$ultimoProtocolo) {
            $sequencial = 1;
        } else {
            $partes = explode('-', $ultimoProtocolo->numero_protocolo);
            $sequencial = (int) end($partes) + 1;
        }

        return sprintf('AFST-%d-%04d', $ano, $sequencial);
    }
}
