<?php

namespace App\Filament\Resources\Jurisdicaos\Pages;

use App\Filament\Resources\Jurisdicaos\JurisdicaoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditJurisdicao extends EditRecord
{
    protected static string $resource = JurisdicaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
