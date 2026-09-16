<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\Lead;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options(array_combine(Lead::STATUSES, Lead::STATUSES))
                    ->required(),
                TextInput::make('name')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('phone')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('email')
                    ->email()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('service')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('message')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }
}
