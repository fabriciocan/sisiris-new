<?php

namespace App\Filament\Resources\Assembleias\Pages;

use App\Filament\Resources\Assembleias\AssembleiaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssembleias extends ListRecords
{
    protected static string $resource = AssembleiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
