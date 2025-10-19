<?php

namespace App\Filament\Resources\CargoGrandeAssembleias\Pages;

use App\Filament\Resources\CargoGrandeAssembleias\CargoGrandeAssembleiaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCargoGrandeAssembleias extends ListRecords
{
    protected static string $resource = CargoGrandeAssembleiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
