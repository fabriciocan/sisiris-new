<?php

namespace App\Filament\Resources\CargoAssembleias\Pages;

use App\Filament\Resources\CargoAssembleias\CargoAssembleiaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCargoAssembleia extends EditRecord
{
    protected static string $resource = CargoAssembleiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
