<?php

namespace App\Livewire;

use App\Models\EventoCalendario;
use App\Models\Assembleia;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CalendarioEventos extends Component
{
    public $currentDate;
    public $currentMonth;
    public $currentYear;
    public $eventos;
    public $assembleia_id = null;
    public $viewMode = 'month'; // month, week, day
    public $showEventModal = false;
    public $selectedEvent = null;
    public $selectedDate = null;
    public $showCreateModal = false;

    // Propriedades para criação de evento
    public $newEvent = [
        'titulo' => '',
        'descricao' => '',
        'tipo' => 'reuniao_ordinaria',
        'data_inicio' => '',
        'data_fim' => '',
        'local' => '',
        'endereco' => '',
        'publico' => false,
        'cor_evento' => '#3B82F6'
    ];

    public function mount()
    {
        $this->currentDate = Carbon::now();
        $this->currentMonth = $this->currentDate->month;
        $this->currentYear = $this->currentDate->year;
        $this->loadEventos();
    }

    public function loadEventos()
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        $query = EventoCalendario::whereBetween('data_inicio', [$startOfMonth, $endOfMonth]);

        if ($this->assembleia_id) {
            $query->where('assembleia_id', $this->assembleia_id);
        }

        $this->eventos = $query->with(['assembleia', 'criadoPor'])->get();
    }

    public function previousPeriod()
    {
        switch ($this->viewMode) {
            case 'month':
                $this->currentDate = $this->currentDate->subMonth();
                break;
            case 'week':
                $this->currentDate = $this->currentDate->subWeek();
                break;
            case 'day':
                $this->currentDate = $this->currentDate->subDay();
                break;
        }
        $this->updateCurrentPeriod();
        $this->loadEventos();
    }

    public function nextPeriod()
    {
        switch ($this->viewMode) {
            case 'month':
                $this->currentDate = $this->currentDate->addMonth();
                break;
            case 'week':
                $this->currentDate = $this->currentDate->addWeek();
                break;
            case 'day':
                $this->currentDate = $this->currentDate->addDay();
                break;
        }
        $this->updateCurrentPeriod();
        $this->loadEventos();
    }

    public function today()
    {
        $this->currentDate = Carbon::now();
        $this->updateCurrentPeriod();
        $this->loadEventos();
    }

    public function updateCurrentPeriod()
    {
        $this->currentMonth = $this->currentDate->month;
        $this->currentYear = $this->currentDate->year;
    }

    public function changeViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->loadEventos();
    }

    public function selectAssembleia($assembleia_id)
    {
        $this->assembleia_id = $assembleia_id;
        $this->loadEventos();
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->newEvent['data_inicio'] = Carbon::parse($date)->format('Y-m-d H:i');
        $this->newEvent['data_fim'] = Carbon::parse($date)->addHour()->format('Y-m-d H:i');
        $this->showCreateModal = true;
    }

    public function showEvent($eventId)
    {
        $this->selectedEvent = EventoCalendario::with(['assembleia', 'criadoPor'])->find($eventId);
        $this->showEventModal = true;
    }

    public function closeEventModal()
    {
        $this->showEventModal = false;
        $this->selectedEvent = null;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->selectedDate = null;
        $this->resetNewEvent();
    }

    public function createEvent()
    {
        $this->validate([
            'newEvent.titulo' => 'required|min:3',
            'newEvent.tipo' => 'required',
            'newEvent.data_inicio' => 'required|date',
            'newEvent.data_fim' => 'nullable|date|after:newEvent.data_inicio',
        ]);

        EventoCalendario::create([
            'titulo' => $this->newEvent['titulo'],
            'descricao' => $this->newEvent['descricao'],
            'tipo' => $this->newEvent['tipo'],
            'data_inicio' => $this->newEvent['data_inicio'],
            'data_fim' => $this->newEvent['data_fim'] ?: $this->newEvent['data_inicio'],
            'local' => $this->newEvent['local'],
            'endereco' => $this->newEvent['endereco'],
            'publico' => $this->newEvent['publico'],
            'cor_evento' => $this->newEvent['cor_evento'],
            'assembleia_id' => $this->assembleia_id ?: Auth::user()->assembleia_id,
            'criado_por' => Auth::id(),
        ]);

        session()->flash('message', 'Evento criado com sucesso!');
        $this->closeCreateModal();
        $this->loadEventos();
    }

    public function resetNewEvent()
    {
        $this->newEvent = [
            'titulo' => '',
            'descricao' => '',
            'tipo' => 'reuniao_ordinaria',
            'data_inicio' => '',
            'data_fim' => '',
            'local' => '',
            'endereco' => '',
            'publico' => false,
            'cor_evento' => '#3B82F6'
        ];
    }

    public function getCalendarDays()
    {
        if ($this->viewMode === 'week') {
            return $this->getWeekDays();
        } elseif ($this->viewMode === 'day') {
            return $this->getDayView();
        }

        // Month view (default)
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        // Começar na segunda-feira da semana que contém o primeiro dia do mês
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        
        // Terminar no domingo da semana que contém o último dia do mês
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);
        
        $days = collect();
        
        $current = $startOfCalendar->copy();
        while ($current <= $endOfCalendar) {
            $dayEvents = $this->eventos->filter(function ($evento) use ($current) {
                return Carbon::parse($evento->data_inicio)->isSameDay($current);
            });
            
            $days->push([
                'day' => $current->day,
                'date' => $current->format('Y-m-d'),
                'isCurrentMonth' => $current->month === $this->currentMonth,
                'isToday' => $current->isToday(),
                'events' => $dayEvents
            ]);
            
            $current->addDay();
        }
        
        return $days;
    }

    public function getWeekDays()
    {
        $startOfWeek = $this->currentDate->copy()->startOfWeek(Carbon::MONDAY);
        $days = collect();
        
        for ($i = 0; $i < 7; $i++) {
            $current = $startOfWeek->copy()->addDays($i);
            $dayEvents = $this->eventos->filter(function ($evento) use ($current) {
                return Carbon::parse($evento->data_inicio)->isSameDay($current);
            });
            
            $days->push([
                'day' => $current->day,
                'date' => $current->format('Y-m-d'),
                'isCurrentMonth' => true,
                'isToday' => $current->isToday(),
                'events' => $dayEvents
            ]);
        }
        
        return $days;
    }

    public function getDayView()
    {
        $dayEvents = $this->eventos->filter(function ($evento) {
            return Carbon::parse($evento->data_inicio)->isSameDay($this->currentDate);
        })->sortBy('data_inicio');
        
        return collect([
            [
                'day' => $this->currentDate->day,
                'date' => $this->currentDate->format('Y-m-d'),
                'isCurrentMonth' => true,
                'isToday' => $this->currentDate->isToday(),
                'events' => $dayEvents
            ]
        ]);
    }

    public function getPeriodTitle()
    {
        switch ($this->viewMode) {
            case 'week':
                $start = $this->currentDate->copy()->startOfWeek(Carbon::MONDAY);
                $end = $this->currentDate->copy()->endOfWeek(Carbon::SUNDAY);
                return $start->format('d/m') . ' - ' . $end->format('d/m/Y');
            case 'day':
                return $this->currentDate->format('l, d \d\e F \d\e Y');
            default: // month
                return $this->currentDate->format('F \d\e Y');
        }
    }

    public function render()
    {
        $assembleias = Assembleia::orderBy('nome')->get();
        $calendarDays = $this->getCalendarDays();
        
        return view('livewire.calendario-eventos-clean', [
            'assembleias' => $assembleias,
            'calendarDays' => $calendarDays
        ]);
    }
}
