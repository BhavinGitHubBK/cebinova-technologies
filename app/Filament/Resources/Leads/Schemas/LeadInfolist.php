<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('business_name')
                    ->placeholder('-'),
                TextEntry::make('phone'),
                TextEntry::make('whatsapp')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('business_type')
                    ->placeholder('-'),
                TextEntry::make('service')
                    ->placeholder('-'),
                TextEntry::make('package_category')
                    ->placeholder('-'),
                TextEntry::make('plan_duration')
                    ->placeholder('-'),
                TextEntry::make('selected_price')
                    ->placeholder('-'),
                TextEntry::make('city')
                    ->placeholder('-'),
                TextEntry::make('budget')
                    ->placeholder('-'),
                TextEntry::make('message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('free_consultation')
                    ->boolean(),
                TextEntry::make('source'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
