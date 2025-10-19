<?php

namespace App\Filament\Resources\CargoAssembleias\Pages;

use App\Filament\Resources\CargoAssembleias\CargoAssembleiaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCargoAssembleias extends ListRecords
{
    protected static string $resource = CargoAssembleiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
