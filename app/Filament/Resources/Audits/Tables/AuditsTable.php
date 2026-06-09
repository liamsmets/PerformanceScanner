<?php

namespace App\Filament\Resources\Audits\Tables;

use App\Models\Audit;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(fn (Audit $record): ?string => $record->website?->name),

                TextColumn::make('target_url')
                    ->label('URL')
                    ->state(fn (Audit $record): ?string => $record->websitePage?->url ?? $record->website?->url)
                    ->limit(40)
                    ->tooltip(fn (Audit $record): ?string => $record->websitePage?->url ?? $record->website?->url)
                    ->copyable()
                    ->url(fn (Audit $record): ?string => $record->websitePage?->url ?? $record->website?->url)
                    ->openUrlInNewTab()
                    ->toggleable(),

                TextColumn::make('performance_score')
                    ->label('Performance')
                    ->badge()
                    ->color(fn (?int $state): string => self::getScoreColor($state))
                    ->sortable(),

                TextColumn::make('accessibility_score')
                    ->label('Accessibility')
                    ->badge()
                    ->color(fn (?int $state): string => self::getScoreColor($state))
                    ->sortable(),

                TextColumn::make('best_practices_score')
                    ->label('Best Practices')
                    ->badge()
                    ->color(fn (?int $state): string => self::getScoreColor($state))
                    ->sortable(),

                TextColumn::make('seo_score')
                    ->label('SEO')
                    ->badge()
                    ->color(fn (?int $state): string => self::getScoreColor($state))
                    ->sortable(),

                TextColumn::make('scanned_at')
                    ->label('Gescand op')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('website')
                    ->label('Website')
                    ->relationship('website', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Type audit')
                    ->options([
                        'website' => 'Hoofdwebsite',
                        'page' => 'Pagina',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'website' => $query->whereNull('website_page_id'),
                            'page' => $query->whereNotNull('website_page_id'),
                            default => $query,
                        };
                    }),
            ])
            ->defaultSort('scanned_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Bekijk'),
            ]);
    }

    private static function getScoreColor(?int $score): string
    {
        if ($score === null) {
            return 'gray';
        }

        if ($score <= 25) {
            return 'danger';
        }

        if ($score <= 50) {
            return 'warning';
        }

        if ($score <= 75) {
            return 'info';
        }

        return 'success';
    }
}
