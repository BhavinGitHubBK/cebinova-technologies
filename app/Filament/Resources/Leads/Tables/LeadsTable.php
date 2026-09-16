<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('service')
                    ->toggleable(),
                TextColumn::make('package_category')
                    ->label('Package')
                    ->toggleable(),
                TextColumn::make('city')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('source')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(array_combine(Lead::STATUSES, Lead::STATUSES)),
                SelectFilter::make('source')
                    ->options(fn (): array => Lead::query()
                        ->whereNotNull('source')
                        ->distinct()
                        ->orderBy('source')
                        ->pluck('source', 'source')
                        ->all()),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(function (Lead $record): ?string {
                        $raw = $record->whatsapp ?: $record->phone;
                        $digits = preg_replace('/\D+/', '', (string) $raw);

                        return $digits ? 'https://wa.me/'.$digits : null;
                    })
                    ->openUrlInNewTab()
                    ->visible(fn (Lead $record): bool => filled($record->whatsapp ?: $record->phone)),
            ]);
    }
}
