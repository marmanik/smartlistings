<?php

namespace App\Filament\Resources\CasafariProperties\Pages;

use App\Filament\Resources\CasafariProperties\CasafariPropertyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCasafariProperties extends ListRecords
{
    protected static string $resource = CasafariPropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
