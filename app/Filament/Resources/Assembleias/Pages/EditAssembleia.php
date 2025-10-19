<?php

namespace App\Filament\Resources\Assembleias\Pages;

use App\Filament\Resources\Assembleias\AssembleiaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAssembleia extends EditRecord
{
    protected static string $resource = AssembleiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
