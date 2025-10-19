<?php

namespace App\Filament\Resources\CargoGrandeAssembleias\Pages;

use App\Filament\Resources\CargoGrandeAssembleias\CargoGrandeAssembleiaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCargoGrandeAssembleia extends EditRecord
{
    protected static string $resource = CargoGrandeAssembleiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
