<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Meus Cargos
        </x-slot>

        @php
            $dados = $this->getCargosData();
            $cargosAtuais = $dados['cargos_atuais'];
            $historicoCargos = $dados['historico_cargos'];
        @endphp

        <!-- Cargos Atuais -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Cargos Atuais</h3>
            
            @if($cargosAtuais->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">Nenhum cargo ativo no momento.</p>
            @else
                <div class="space-y-3">
                    @foreach($cargosAtuais as $cargo)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $cargo['nome'] }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $cargo['tipo'] }} • Desde {{ $cargo['data_inicio']->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $cargo['categoria'] === 'administrativo' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $cargo['categoria'] === 'ritual' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $cargo['categoria'] === 'executivo' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ !in_array($cargo['categoria'], ['administrativo', 'ritual', 'executivo']) ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ ucfirst($cargo['categoria']) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Histórico de Cargos -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Histórico de Cargos</h3>
            
            @if($historicoCargos->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">Nenhum cargo anterior encontrado.</p>
            @else
                <div class="space-y-2">
                    @foreach($historicoCargos as $cargo)
                        <div class="flex items-center justify-between p-2 border-l-4 border-gray-300 dark:border-gray-600 pl-3">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $cargo['nome'] }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $cargo['tipo'] }} • 
                                    {{ $cargo['data_inicio']->format('d/m/Y') }} - {{ $cargo['data_fim']->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    {{ $cargo['categoria'] === 'administrativo' ? 'bg-blue-50 text-blue-700' : '' }}
                                    {{ $cargo['categoria'] === 'ritual' ? 'bg-purple-50 text-purple-700' : '' }}
                                    {{ $cargo['categoria'] === 'executivo' ? 'bg-green-50 text-green-700' : '' }}
                                    {{ !in_array($cargo['categoria'], ['administrativo', 'ritual', 'executivo']) ? 'bg-gray-50 text-gray-700' : '' }}
                                ">
                                    {{ ucfirst($cargo['categoria']) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>