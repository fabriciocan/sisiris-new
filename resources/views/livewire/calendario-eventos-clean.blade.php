<div class="filament-page-calendar">
    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0;
        }
        
        .calendar-cell {
            height: 8rem;
            border: 1px solid rgb(229 231 235);
            padding: 0.5rem;
        }
        
        .dark .calendar-cell {
            border-color: rgb(75 85 99);
        }
        
        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0;
        }
    </style>

    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-400/10 dark:text-success-400">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                {{ $this->getPeriodTitle() }}
            </h2>
            
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Assembleia:</label>
                <select wire:model.live="assembleia_id" 
                        class="block rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Todas</option>
                    @foreach($assembleias as $assembleia)
                        <option value="{{ $assembleia->id }}">{{ $assembleia->nome }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <div class="flex rounded-lg bg-gray-50 p-1 dark:bg-gray-800">
                <button wire:click="changeViewMode('month')" 
                        class="rounded-md px-3 py-1 text-sm font-medium {{ $viewMode === 'month' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                    Mês
                </button>
                <button wire:click="changeViewMode('week')" 
                        class="rounded-md px-3 py-1 text-sm font-medium {{ $viewMode === 'week' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                    Semana
                </button>
                <button wire:click="changeViewMode('day')" 
                        class="rounded-md px-3 py-1 text-sm font-medium {{ $viewMode === 'day' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                    Dia
                </button>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1">
                    <a href="{{ route('calendario.export.ical', ['assembleia_id' => $assembleia_id]) }}" 
                       class="inline-flex items-center rounded-lg bg-success-600 px-2 py-1 text-xs font-medium text-white hover:bg-success-500"
                       title="Exportar para iCal">
                        📅 iCal
                    </a>
                </div>

                <button wire:click="previousPeriod" 
                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                
                <button wire:click="today" 
                        class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-1 text-sm font-medium text-white hover:bg-primary-500">
                    Hoje
                </button>
                
                <button wire:click="nextPeriod" 
                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    @if($viewMode === 'month')
        <div class="overflow-hidden rounded-lg bg-white shadow ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
            {{-- Cabeçalho dos dias da semana --}}
            <div class="calendar-header bg-gray-50 dark:bg-gray-900">
                @foreach(['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $dia)
                    <div class="border-r border-gray-200 py-3 text-center text-sm font-semibold text-gray-900 last:border-r-0 dark:border-gray-700 dark:text-white">
                        {{ $dia }}
                    </div>
                @endforeach
            </div>
            
            {{-- Grid do calendário --}}
            <div class="calendar-grid">
                @foreach($calendarDays as $day)
                    <div class="calendar-cell group relative border-b border-r border-gray-200 last:border-r-0 dark:border-gray-700 {{ $day['isCurrentMonth'] ? 'bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700/50' : 'bg-gray-50 dark:bg-gray-900/50' }}">
                        {{-- Número do dia --}}
                        <div class="flex items-start justify-between">
                            @if($day['isToday'])
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-600 text-xs font-semibold text-white">
                                    {{ $day['day'] }}
                                </span>
                            @else
                                <span class="text-sm {{ $day['isCurrentMonth'] ? 'font-medium text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600' }}">
                                    {{ $day['day'] }}
                                </span>
                            @endif
                            
                            {{-- Botão de adicionar evento --}}
                            @if($day['isCurrentMonth'])
                                <button wire:click="openCreateModal('{{ $day['date'] }}')" 
                                        class="flex h-5 w-5 items-center justify-center rounded text-gray-400 opacity-0 transition-opacity hover:bg-gray-100 hover:text-primary-600 group-hover:opacity-100 dark:hover:bg-gray-700 dark:hover:text-primary-400" 
                                        title="Adicionar evento">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                        
                        {{-- Eventos do dia --}}
                        <div class="mt-1 space-y-1">
                            @foreach($day['events'] as $evento)
                                <div wire:click="showEventModal({{ $evento->id }})" 
                                     class="cursor-pointer truncate rounded px-1 py-0.5 text-xs font-medium text-white transition-opacity hover:opacity-80"
                                     style="background-color: {{ $evento->cor_evento }};"
                                     title="{{ $evento->titulo }}">
                                    {{ Str::limit($evento->titulo, 15) }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($showEventModal && $selectedEvent)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEventModal"></div>
                
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                
                <div class="relative inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="bg-white px-4 pt-5 pb-4 dark:bg-gray-800 sm:p-6 sm:pb-4">
                        <div class="flex items-start justify-between">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                {{ $selectedEvent->titulo }}
                            </h3>
                            <button wire:click="closeEventModal" class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-gray-500 dark:hover:text-gray-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mt-4 space-y-4">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo:</span>
                                <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $selectedEvent->tipo)) }}</span>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Data de Início:</span>
                                <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ $selectedEvent->data_inicio->format('d/m/Y H:i') }}</span>
                            </div>
                            
                            @if($selectedEvent->data_fim)
                                <div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Data de Fim:</span>
                                    <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ $selectedEvent->data_fim->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                            
                            @if($selectedEvent->local)
                                <div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Local:</span>
                                    <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ $selectedEvent->local }}</span>
                                </div>
                            @endif
                            
                            @if($selectedEvent->endereco)
                                <div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Endereço:</span>
                                    <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ $selectedEvent->endereco }}</span>
                                </div>
                            @endif
                            
                            @if($selectedEvent->descricao)
                                <div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Descrição:</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedEvent->descricao }}</p>
                                </div>
                            @endif
                            
                            <div class="flex items-center gap-2">
                                <a href="{{ route('calendario.google', $selectedEvent) }}" 
                                   target="_blank"
                                   class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-500">
                                    + Google Calendar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCreateModal"></div>
                
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                
                <div class="relative inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form wire:submit.prevent="createEvent">
                        <div class="bg-white px-4 pt-5 pb-4 dark:bg-gray-800 sm:p-6 sm:pb-4">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                    Criar Novo Evento
                                </h3>
                                <button type="button" wire:click="closeCreateModal" class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-gray-500 dark:hover:text-gray-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Título</label>
                                    <input type="text" wire:model="newEvent.titulo" 
                                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                                    <select wire:model="newEvent.tipo" 
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                        <option value="reuniao_ordinaria">Reunião Ordinária</option>
                                        <option value="reuniao_extraordinaria">Reunião Extraordinária</option>
                                        <option value="sessao_magna">Sessão Magna</option>
                                        <option value="evento_social">Evento Social</option>
                                        <option value="curso">Curso</option>
                                        <option value="palestra">Palestra</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Início</label>
                                        <input type="datetime-local" wire:model="newEvent.data_inicio" 
                                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Fim</label>
                                        <input type="datetime-local" wire:model="newEvent.data_fim" 
                                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Local</label>
                                    <input type="text" wire:model="newEvent.local" 
                                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Endereço</label>
                                    <input type="text" wire:model="newEvent.endereco" 
                                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
                                    <textarea wire:model="newEvent.descricao" rows="3" 
                                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cor do Evento</label>
                                        <input type="color" wire:model="newEvent.cor_evento" 
                                               class="mt-1 block h-10 w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600">
                                    </div>
                                    
                                    <div class="flex items-center pt-6">
                                        <input type="checkbox" wire:model="newEvent.publico" 
                                               class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Evento público</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 dark:bg-gray-700 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" 
                                    class="inline-flex w-full justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 sm:ml-3 sm:w-auto">
                                Criar Evento
                            </button>
                            <button type="button" wire:click="closeCreateModal"
                                    class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-300 dark:ring-gray-500 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>