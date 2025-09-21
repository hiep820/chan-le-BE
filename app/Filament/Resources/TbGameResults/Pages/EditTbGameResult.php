<?php

namespace App\Filament\Resources\TbGameResults\Pages;

use App\Filament\Resources\TbGameResults\TbGameResultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTbGameResult extends EditRecord
{
    protected static string $resource = TbGameResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
