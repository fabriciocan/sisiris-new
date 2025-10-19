<?php

namespace App\Filament\Resources\EventoCalendarios\Pages;

use App\Filament\Resources\EventoCalendarios\EventoCalendarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventoCalendarios extends ListRecords
{
    protected static string $resource = EventoCalendarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
