<div class="space-y-6">
    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('message') }}
        </div>
    @endif

    <!-- Cabeçalho do Calendário -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $this->getPeriodTitle() }}
            </h2>
            
            <!-- Filtro por Assembleia -->
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Assembleia:</label>
                <select wire:model.live="assembleia_id" class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    <option value="">Todas</option>
                    @foreach($assembleias as $assembleia)
                        <option value="{{ $assembleia->id }}">{{ $assembleia->nome }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="flex items-center space-x-2">
            <!-- Botões de Visualização -->
            <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button wire:click="changeViewMode('month')" 
                        class="px-3 py-1 text-sm rounded-md {{ $viewMode === 'month' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400' }}">
                    Mês
                </button>
                <button wire:click="changeViewMode('week')" 
                        class="px-3 py-1 text-sm rounded-md {{ $viewMode === 'week' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400' }}">
                    Semana
                </button>
                <button wire:click="changeViewMode('day')" 
                        class="px-3 py-1 text-sm rounded-md {{ $viewMode === 'day' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400' }}">
                    Dia
                </button>
            </div>

            <!-- Navegação do Calendário -->
            <div class="flex items-center space-x-2">
                <!-- Botões de Exportação -->
                <div class="flex items-center space-x-1 mr-2">
                    <a href="{{ route('calendario.export.ical', ['assembleia_id' => $assembleia_id]) }}" 
                       class="px-2 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600"
                       title="Exportar como iCal">
                        📅 iCal
                    </a>
                </div>

                <button wire:click="today" class="px-3 py-2 text-sm bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Hoje
                </button>
                <button wire:click="previousPeriod" class="p-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <button wire:click="nextPeriod" class="p-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Grid do Calendário -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        @if($viewMode === 'month')
            <!-- Visualização Mensal -->
            <!-- Cabeçalho dos dias da semana -->
            <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
                @foreach(['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'] as $day)
                    <div class="p-3 text-center text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">
                        {{ $day }}
                    </div>
                @endforeach
            </div>

            <!-- Grid dos dias -->
            <div class="grid grid-cols-7">
                @foreach($calendarDays as $index => $day)
                    <div class="min-h-[120px] border-r border-b border-gray-200 dark:border-gray-700 {{ !$day['isCurrentMonth'] ? 'bg-gray-50 dark:bg-gray-900' : 'bg-white dark:bg-gray-800' }} {{ $day['isToday'] ? 'bg-blue-50 dark:bg-blue-900/20' : '' }} cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                         wire:click="selectDate('{{ $day['date']->format('Y-m-d') }}')">
                        <!-- Número do dia -->
                        <div class="p-2">
                            <span class="text-sm {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600' }} {{ $day['isToday'] ? 'font-bold text-blue-600 dark:text-blue-400' : '' }}">
                                {{ $day['date']->day }}
                            </span>
                        </div>

                        <!-- Eventos do dia -->
                        <div class="px-2 pb-2 space-y-1">
                            @foreach($day['eventos'] as $evento)
                                <div class="text-xs p-1 rounded truncate cursor-pointer hover:shadow-md" 
                                     style="background-color: {{ $evento->cor_evento }}20; border-left: 3px solid {{ $evento->cor_evento }};"
                                     wire:click.stop="showEvent({{ $evento->id }})"
                                     title="{{ $evento->titulo }} - {{ \Carbon\Carbon::parse($evento->data_inicio)->format('H:i') }}">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ Str::limit($evento->titulo, 15) }}
                                    </div>
                                    <div class="text-gray-600 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($evento->data_inicio)->format('H:i') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        @elseif($viewMode === 'week')
            <!-- Visualização Semanal -->
            <div class="grid grid-cols-7">
                @foreach($calendarDays as $day)
                    <div class="border-r border-gray-200 dark:border-gray-700">
                        <div class="p-3 text-center border-b border-gray-200 dark:border-gray-700 {{ $day['isToday'] ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-50 dark:bg-gray-900' }}">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $day['date']->format('D') }}</div>
                            <div class="text-lg {{ $day['isToday'] ? 'font-bold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $day['date']->day }}</div>
                        </div>
                        
                        <div class="min-h-[400px] p-2 space-y-2">
                            @foreach($day['eventos'] as $evento)
                                <div class="p-2 rounded text-xs cursor-pointer hover:shadow-md" 
                                     style="background-color: {{ $evento->cor_evento }}20; border-left: 3px solid {{ $evento->cor_evento }};"
                                     wire:click="showEvent({{ $evento->id }})">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $evento->titulo }}</div>
                                    <div class="text-gray-600 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($evento->data_inicio)->format('H:i') }}
                                        @if($evento->data_fim)
                                            - {{ \Carbon\Carbon::parse($evento->data_fim)->format('H:i') }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        @else
            <!-- Visualização Diária -->
            <div class="p-6">
                <div class="text-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $currentDate->format('l, d \d\e F \d\e Y') }}
                    </h3>
                </div>
                
                @if($calendarDays->first()['eventos']->count() > 0)
                    <div class="space-y-4">
                        @foreach($calendarDays->first()['eventos'] as $evento)
                            <div class="flex items-start space-x-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md cursor-pointer"
                                 wire:click="showEvent({{ $evento->id }})">
                                <div class="w-1 h-16 rounded" style="background-color: {{ $evento->cor_evento }};"></div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $evento->titulo }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $evento->descricao }}</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ \Carbon\Carbon::parse($evento->data_inicio)->format('H:i') }}</span>
                                        @if($evento->local)
                                            <span>{{ $evento->local }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nenhum evento</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Não há eventos agendados para este dia.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Legenda de tipos de eventos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Tipos de Eventos</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $tiposEventos = [
                    'reuniao_ordinaria' => ['nome' => 'Reunião Ordinária', 'cor' => '#3B82F6'],
                    'reuniao_extraordinaria' => ['nome' => 'Reunião Extraordinária', 'cor' => '#EF4444'],
                    'assembleia_geral' => ['nome' => 'Assembleia Geral', 'cor' => '#8B5CF6'],
                    'assembleia_extraordinaria' => ['nome' => 'Assembleia Extraordinária', 'cor' => '#F59E0B'],
                    'sessao_magna' => ['nome' => 'Sessão Magna', 'cor' => '#10B981'],
                    'iniciacao' => ['nome' => 'Iniciação', 'cor' => '#6366F1'],
                    'elevacao' => ['nome' => 'Elevação', 'cor' => '#EC4899'],
                    'exaltacao' => ['nome' => 'Exaltação', 'cor' => '#84CC16']
                ];
            @endphp
            
            @foreach($tiposEventos as $tipo => $info)
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 rounded" style="background-color: {{ $info['cor'] }}"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $info['nome'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

    <!-- Modal de Detalhes do Evento -->
    @if($showEventModal && $selectedEvent)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEventModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                        {{ $selectedEvent->titulo }}
                                    </h3>
                                    <button wire:click="closeEventModal" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 rounded" style="background-color: {{ $selectedEvent->cor_evento }};"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400 capitalize">
                                            {{ str_replace('_', ' ', $selectedEvent->tipo) }}
                                        </span>
                                    </div>
                                    
                                    @if($selectedEvent->descricao)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Descrição</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedEvent->descricao }}</p>
                                        </div>
                                    @endif
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Data e Hora</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($selectedEvent->data_inicio)->format('d/m/Y H:i') }}
                                            @if($selectedEvent->data_fim)
                                                - {{ \Carbon\Carbon::parse($selectedEvent->data_fim)->format('H:i') }}
                                            @endif
                                        </p>
                                    </div>
                                    
                                    @if($selectedEvent->local)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Local</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedEvent->local }}</p>
                                            @if($selectedEvent->endereco)
                                                <p class="text-xs text-gray-500 dark:text-gray-500">{{ $selectedEvent->endereco }}</p>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if($selectedEvent->assembleia)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Assembleia</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedEvent->assembleia->nome }}</p>
                                        </div>
                                    @endif
                                    
                                    <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="flex items-center">
                                            @if($selectedEvent->publico)
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Público
                                            @else
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464M9.878 9.878l-2.121-2.121m2.121 2.121l4.242 4.242M9.878 9.878l4.242 4.242m0 0L15.535 15.535M9.878 9.878l-2.121-2.121"></path>
                                                </svg>
                                                Privado
                                            @endif
                                        </span>
                                        <span>Criado por {{ $selectedEvent->criadoPor->name }}</span>
                                    </div>
                                    
                                    <!-- Botões de Ação -->
                                    <div class="flex items-center space-x-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <a href="{{ route('calendario.google', $selectedEvent->id) }}" 
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                            📅 Adicionar ao Google Calendar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Criação de Evento -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCreateModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="createEvent">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    Novo Evento
                                </h3>
                                <button type="button" wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título *</label>
                                    <input type="text" wire:model="newEvent.titulo" 
                                           class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    @error('newEvent.titulo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo *</label>
                                    <select wire:model="newEvent.tipo" 
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="reuniao_ordinaria">Reunião Ordinária</option>
                                        <option value="reuniao_extraordinaria">Reunião Extraordinária</option>
                                        <option value="assembleia_geral">Assembleia Geral</option>
                                        <option value="assembleia_extraordinaria">Assembleia Extraordinária</option>
                                        <option value="sessao_magna">Sessão Magna</option>
                                        <option value="iniciacao">Iniciação</option>
                                        <option value="elevacao">Elevação</option>
                                        <option value="exaltacao">Exaltação</option>
                                    </select>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data/Hora Início *</label>
                                        <input type="datetime-local" wire:model="newEvent.data_inicio" 
                                               class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        @error('newEvent.data_inicio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data/Hora Fim</label>
                                        <input type="datetime-local" wire:model="newEvent.data_fim" 
                                               class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        @error('newEvent.data_fim') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição</label>
                                    <textarea wire:model="newEvent.descricao" rows="3"
                                              class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Local</label>
                                        <input type="text" wire:model="newEvent.local" 
                                               class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cor do Evento</label>
                                        <input type="color" wire:model="newEvent.cor_evento" 
                                               class="w-full h-10 rounded-md border-gray-300 dark:border-gray-600">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Endereço</label>
                                    <input type="text" wire:model="newEvent.endereco" 
                                           class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                                
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="newEvent.publico" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Evento público</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Criar Evento
                            </button>
                            <button type="button" wire:click="closeCreateModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-700">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
