<?php

namespace App\Filament\Resources\Comissoes\Pages;

use App\Filament\Resources\Comissoes\ComissaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComissoes extends ListRecords
{
    protected static string $resource = ComissaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
