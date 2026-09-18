<?php

namespace App\Filament\Resources\MarketingPackages\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MarketingPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Slug used in URLs and helper lookups (e.g. regular).'),
                TextInput::make('title')
                    ->required(),
                TextInput::make('service')
                    ->required()
                    ->helperText('Category label used on the contact form (usually matches title).'),
                TextInput::make('heading'),
                Textarea::make('subheading')
                    ->rows(2)
                    ->columnSpanFull(),
                Textarea::make('teaser')
                    ->rows(2)
                    ->columnSpanFull(),
                TextInput::make('best_if'),
                TextInput::make('badge'),
                TextInput::make('cta'),
                TextInput::make('secondary_cta'),
                Textarea::make('why')
                    ->rows(3)
                    ->columnSpanFull(),
                TagsInput::make('includes')
                    ->helperText('Package-level includes (used by Complete Growth).')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->required()
                    ->default(0),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}
