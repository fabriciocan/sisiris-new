<div class="space-y-4">
    <x-filament::section>
        <x-slot name="heading">
            Meninas Selecionadas ({{ $membros->count() }})
        </x-slot>
        
        <x-slot name="description">
            Lista das meninas ativas selecionadas para a cerimônia de maioridade
        </x-slot>
        
        <div class="space-y-3">
            @forelse($membros as $membro)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                <span class="text-primary-600 font-medium text-sm">
                                    {{ substr($membro->nome_completo, 0, 2) }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $membro->nome_completo }}</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p>Data de Nascimento: {{ $membro->data_nascimento->format('d/m/Y') }}</p>
                                <p>Data de Iniciação: {{ $membro->data_iniciacao->format('d/m/Y') }}</p>
                                @if($membro->madrinha)
                                    <p>Madrinha: {{ $membro->madrinha }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex-shrink-0">
                        <x-filament::badge color="success">
                            Menina Ativa
                        </x-filament::badge>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="text-gray-400 text-lg mb-2">
                        <x-heroicon-o-user-group class="w-12 h-12 mx-auto" />
                    </div>
                    <p class="text-gray-500">Nenhuma menina selecionada</p>
                </div>
            @endforelse
        </div>
        
        @if($membros->count() > 0)
            <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-start space-x-3">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
                    <div class="text-sm text-blue-700">
                        <p class="font-medium">Informações importantes:</p>
                        <ul class="mt-1 list-disc list-inside space-y-1">
                            <li>Todas as meninas listadas são elegíveis para a cerimônia de maioridade</li>
                            <li>Após a aprovação, o status das meninas será atualizado automaticamente</li>
                            <li>A data da cerimônia será registrada no perfil de cada menina</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</div>