<?php

namespace App\Filament\Resources\Comissoes\Pages;

use App\Filament\Resources\Comissoes\ComissaoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComissao extends EditRecord
{
    protected static string $resource = ComissaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
