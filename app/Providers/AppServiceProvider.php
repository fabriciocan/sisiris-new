<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Membro;
use App\Models\CargoAssembleia;
use App\Models\CargoGrandeAssembleia;
use App\Models\Protocolo;
use App\Models\Ticket;
use App\Observers\MembroObserver;
use App\Observers\CargoAssembleiaObserver;
use App\Observers\CargoGrandeAssembleiaObserver;
use App\Observers\ProtocoloObserver;
use App\Observers\TicketObserver;
use Livewire\Livewire;
use App\Livewire\CalendarioEventos;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Observers
        Membro::observe(MembroObserver::class);
        CargoAssembleia::observe(CargoAssembleiaObserver::class);
        CargoGrandeAssembleia::observe(CargoGrandeAssembleiaObserver::class);
        Protocolo::observe(ProtocoloObserver::class);
        Ticket::observe(TicketObserver::class);

        // Registrar componentes Livewire explicitamente
        Livewire::component('calendario-eventos', CalendarioEventos::class);
    }
}
