<?php

namespace App\Filament\Resources\TbGameResults\Pages;

use App\Filament\Resources\TbGameResults\TbGameResultResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTbGameResults extends ListRecords
{
    protected static string $resource = TbGameResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
