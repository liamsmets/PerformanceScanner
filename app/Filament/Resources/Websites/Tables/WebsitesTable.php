<?php

namespace App\Filament\Resources\Websites\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class WebsitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('url')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('performance_score')
                    ->label('Score')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state >= 90 => 'success',
                        $state >= 50 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('last_scanned_at')
                    ->label('Gescand op')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
