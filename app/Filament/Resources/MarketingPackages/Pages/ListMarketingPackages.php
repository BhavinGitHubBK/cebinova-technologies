<?php

namespace App\Filament\Resources\MarketingPackages\Pages;

use App\Filament\Resources\MarketingPackages\MarketingPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketingPackages extends ListRecords
{
    protected static string $resource = MarketingPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
