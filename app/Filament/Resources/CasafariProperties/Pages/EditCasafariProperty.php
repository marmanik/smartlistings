<?php

namespace App\Filament\Resources\CasafariProperties\Pages;

use App\Filament\Resources\CasafariProperties\CasafariPropertyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCasafariProperty extends EditRecord
{
    protected static string $resource = CasafariPropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
