<?php

namespace App\Filament\Resources\MarketingPackages\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlansRelationManager extends RelationManager
{
    protected static string $relationship = 'plans';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required()
                    ->helperText('e.g. monthly, yearly'),
                TextInput::make('label')
                    ->required()
                    ->helperText('Shown on forms (Monthly, Quarterly, …)'),
                TextInput::make('duration'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('period')
                    ->placeholder('/ month'),
                TextInput::make('badge'),
                TextInput::make('cta'),
                Textarea::make('note')
                    ->rows(2)
                    ->columnSpanFull(),
                TagsInput::make('includes')
                    ->columnSpanFull(),
                TagsInput::make('monthly_pace')
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('key')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('price')
                    ->money('INR', locale: 'en_IN')
                    ->sortable(),
                TextColumn::make('period'),
                TextColumn::make('badge')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
