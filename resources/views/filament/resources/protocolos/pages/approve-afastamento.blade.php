@push('styles')
<link rel="stylesheet" href="{{ asset('css/approve-protocolo.css') }}">
@endpush

<x-filament-panels::page>
    {{-- Informações do Protocolo --}}
    <x-filament::section>
        <x-slot name="heading">
            Informações do Protocolo
        </x-slot>
        <x-slot name="description">
            Dados gerais do protocolo de afastamento
        </x-slot>

        <div class="protocolo-info-grid">
            <div class="protocolo-field">
                <div class="protocolo-field-label">Número do Protocolo</div>
                <div class="protocolo-field-value">{{ $this->record->numero_protocolo }}</div>
            </div>

            <div class="protocolo-field">
                <div class="protocolo-field-label">Status</div>
                <div class="protocolo-field-value">
                    <span class="protocolo-badge {{
                        match($this->record->status) {
                            'pendente' => 'badge-warning',
                            'em_analise' => 'badge-info',
                            'aprovado' => 'badge-success',
                            'rejeitado' => 'badge-danger',
                            default => 'badge-warning'
                        }
                    }}">
                        {{ $this->getStatusLabel($this->record->status) }}
                    </span>
                </div>
            </div>

            <div class="protocolo-field">
                <div class="protocolo-field-label">Data de Solicitação</div>
                <div class="protocolo-field-value">{{ $this->record->data_solicitacao->format('d/m/Y H:i') }}</div>
            </div>

            <div class="protocolo-field">
                <div class="protocolo-field-label">Solicitante</div>
                <div class="protocolo-field-value">{{ $this->record->solicitante->name ?? 'N/A' }}</div>
            </div>
        </div>
    </x-filament::section>

    {{-- Dados do Membro --}}
    <x-filament::section>
        <x-slot name="heading">
            Membro a ser Afastado
        </x-slot>
        <x-slot name="description">
            Informações do membro que será afastado
        </x-slot>

        <div class="protocolo-info-grid">
            <div class="protocolo-field">
                <div class="protocolo-field-label">Nome Completo</div>
                <div class="protocolo-field-value">{{ $this->record->membro->nome_completo ?? 'N/A' }}</div>
            </div>

            <div class="protocolo-field">
                <div class="protocolo-field-label">Número de Membro</div>
                <div class="protocolo-field-value">{{ $this->record->membro->numero_membro ?? 'N/A' }}</div>
            </div>

            <div class="protocolo-field">
                <div class="protocolo-field-label">Status Atual</div>
                <div class="protocolo-field-value">
                    <span class="protocolo-badge {{ $this->record->membro->status === 'ativa' ? 'badge-success' : 'badge-danger' }}">
                        {{ ucfirst($this->record->membro->status) ?? 'N/A' }}
                    </span>
                </div>
            </div>

            <div class="protocolo-field">
                <div class="protocolo-field-label">Data de Iniciação</div>
                <div class="protocolo-field-value">
                    {{ $this->record->membro->data_iniciacao ? $this->record->membro->data_iniciacao->format('d/m/Y') : 'N/A' }}
                </div>
            </div>

            @if($this->record->dados_json['data_afastamento'] ?? null)
            <div class="protocolo-field">
                <div class="protocolo-field-label">Data do Afastamento</div>
                <div class="protocolo-field-value">{{ $this->record->dados_json['data_afastamento'] }}</div>
            </div>
            @endif

            @if($this->record->dados_json['motivo_afastamento'] ?? null)
            <div class="protocolo-field protocolo-field-full">
                <div class="protocolo-field-label">Motivo do Afastamento</div>
                <div class="protocolo-field-value">{{ $this->record->dados_json['motivo_afastamento'] }}</div>
            </div>
            @endif

            @if($this->record->observacoes)
            <div class="protocolo-field protocolo-field-full">
                <div class="protocolo-field-label">Observações</div>
                <div class="protocolo-field-value">{{ $this->record->observacoes }}</div>
            </div>
            @endif
        </div>
    </x-filament::section>

    {{-- Timeline de Logs --}}
    @if($this->record->historico()->exists())
        <x-filament::section collapsible>
            <x-slot name="heading">
                Histórico do Protocolo
            </x-slot>
            <x-slot name="description">
                Timeline de todas as ações realizadas
            </x-slot>

            <div class="historico-timeline">
                @foreach($this->record->historico()->with('user')->orderBy('created_at', 'desc')->get() as $log)
                    <div class="historico-item">
                        <div class="historico-icon-container">
                            <svg class="historico-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="historico-content">
                            <div class="historico-header">
                                <div class="historico-acao">{{ $log->acao_label }}</div>
                                <div class="historico-timestamp">{{ $log->created_at->diffForHumans() }}</div>
                            </div>
                            @if($log->descricao)
                                <div class="historico-descricao">{{ $log->descricao }}</div>
                            @endif
                            @if($log->comentario)
                                <div class="historico-comentario">{{ $log->comentario }}</div>
                            @endif
                            <div class="historico-meta">
                                Por: {{ $log->nome_usuario }} em {{ $log->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
