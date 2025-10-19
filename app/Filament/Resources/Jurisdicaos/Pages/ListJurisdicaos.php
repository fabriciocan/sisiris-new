<?php

namespace App\Filament\Resources\Jurisdicaos\Pages;

use App\Filament\Resources\Jurisdicaos\JurisdicaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJurisdicaos extends ListRecords
{
    protected static string $resource = JurisdicaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
