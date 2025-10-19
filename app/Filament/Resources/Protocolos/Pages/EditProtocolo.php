<?php

namespace App\Filament\Resources\Protocolos\Pages;

use App\Filament\Resources\Protocolos\ProtocoloResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProtocolo extends EditRecord
{
    protected static string $resource = ProtocoloResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
