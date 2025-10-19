<?php

namespace App\Filament\Resources\EventoCalendarios\Pages;

use App\Filament\Resources\EventoCalendarios\EventoCalendarioResource;
use Filament\Resources\Pages\Page;

class CalendarioView extends Page
{
    protected static string $resource = EventoCalendarioResource::class;

    protected static ?string $navigationLabel = 'Visualização do Calendário';

    protected static ?string $title = 'Calendário de Eventos';

    protected string $view = 'filament.resources.evento-calendarios.pages.calendario-direct';

    protected static string $routePath = '/calendario';
}
