<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-filament::section>
                <x-slot name="heading">
                    Informações Básicas
                </x-slot>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Número:</span>
                        <span class="text-gray-900">{{ $protocolo->numero_protocolo }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Assembleia:</span>
                        <span class="text-gray-900">{{ $protocolo->assembleia->nome }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Solicitante:</span>
                        <span class="text-gray-900">{{ $protocolo->solicitante->name }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Data Solicitação:</span>
                        <span class="text-gray-900">{{ $protocolo->data_solicitacao->format('d/m/Y') }}</span>
                    </div>
                </div>
            </x-slot>
        </div>
        
        <div>
            <x-filament::section>
                <x-slot name="heading">
                    Status Atual
                </x-slot>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Etapa:</span>
                        <x-filament::badge color="warning">
                            {{ ucfirst(str_replace('_', ' ', $protocolo->etapa_atual)) }}
                        </x-filament::badge>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Status:</span>
                        <x-filament::badge color="info">
                            {{ ucfirst($protocolo->status) }}
                        </x-filament::badge>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Prioridade:</span>
                        <x-filament::badge color="{{ $protocolo->prioridade === 'alta' ? 'danger' : ($protocolo->prioridade === 'urgente' ? 'warning' : 'success') }}">
                            {{ ucfirst($protocolo->prioridade) }}
                        </x-filament::badge>
                    </div>
                </div>
            </x-slot>
        </div>
    </div>
    
    @if($protocolo->descricao)
        <x-filament::section>
            <x-slot name="heading">
                Descrição
            </x-slot>
            
            <p class="text-gray-700">{{ $protocolo->descricao }}</p>
        </x-filament::section>
    @endif
</div>