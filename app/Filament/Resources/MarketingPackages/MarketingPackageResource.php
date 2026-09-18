<?php

namespace App\Filament\Resources\MarketingPackages;

use App\Filament\Resources\MarketingPackages\Pages\CreateMarketingPackage;
use App\Filament\Resources\MarketingPackages\Pages\EditMarketingPackage;
use App\Filament\Resources\MarketingPackages\Pages\ListMarketingPackages;
use App\Filament\Resources\MarketingPackages\RelationManagers\PlansRelationManager;
use App\Filament\Resources\MarketingPackages\Schemas\MarketingPackageForm;
use App\Filament\Resources\MarketingPackages\Tables\MarketingPackagesTable;
use App\Models\MarketingPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MarketingPackageResource extends Resource
{
    protected static ?string $model = MarketingPackage::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Marketing Packages';

    protected static ?string $modelLabel = 'Marketing Package';

    protected static ?string $pluralModelLabel = 'Marketing Packages';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MarketingPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingPackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PlansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketingPackages::route('/'),
            'create' => CreateMarketingPackage::route('/create'),
            'edit' => EditMarketingPackage::route('/{record}/edit'),
        ];
    }
}
