<?php

namespace App\Filament\Resources\WebsitePages\Widgets;

use App\Models\WebsitePage;
use Filament\Widgets\ChartWidget;

class WebsitePageScoresHistoryChart extends ChartWidget
{
    public ?WebsitePage $record = null;

    protected ?string $heading = '1. Lighthouse scores';

    protected function getData(): array
    {
        $audits = $this->record
            ->audits()
            ->orderBy('scanned_at')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Performance',
                    'data' => $audits->pluck('performance_score')->toArray(),
                    'borderColor' => '#facc15',
                    'backgroundColor' => '#facc15',
                ],
                [
                    'label' => 'Accessibility',
                    'data' => $audits->pluck('accessibility_score')->toArray(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => '#22c55e',
                ],
                [
                    'label' => 'Best Practices',
                    'data' => $audits->pluck('best_practices_score')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'SEO',
                    'data' => $audits->pluck('seo_score')->toArray(),
                    'borderColor' => '#a855f7',
                    'backgroundColor' => '#a855f7',
                ],
            ],
            'labels' => $audits
                ->map(fn ($audit) => $audit->scanned_at?->format('d/m H:i'))
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
