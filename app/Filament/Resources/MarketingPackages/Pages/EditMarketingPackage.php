<?php

namespace App\Filament\Resources\MarketingPackages\Pages;

use App\Filament\Resources\MarketingPackages\MarketingPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketingPackage extends EditRecord
{
    protected static string $resource = MarketingPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
