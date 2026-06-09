<?php

namespace App\Filament\Resources\Websites\Widgets;

use App\Models\Website;
use Filament\Widgets\ChartWidget;

class WebsiteCoreWebVitalsHistoryChart extends ChartWidget
{
    public ?Website $record = null;

    protected ?string $heading = '2. Laadtijden / blocking time';

    protected function getData(): array
    {
        $audits = $this->record
            ->audits()
            ->whereNull('website_page_id')
            ->orderBy('scanned_at')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'LCP (ms)',
                    'data' => $audits
                        ->pluck('lcp_ms')
                        ->map(fn ($value) => $value !== null ? round((float) $value) : null)
                        ->toArray(),
                    'borderColor' => '#f97316',
                    'backgroundColor' => '#f97316',
                ],
                [
                    'label' => 'FCP (ms)',
                    'data' => $audits
                        ->pluck('fcp_ms')
                        ->map(fn ($value) => $value !== null ? round((float) $value) : null)
                        ->toArray(),
                    'borderColor' => '#06b6d4',
                    'backgroundColor' => '#06b6d4',
                ],
                [
                    'label' => 'TBT (ms)',
                    'data' => $audits
                        ->pluck('tbt_ms')
                        ->map(fn ($value) => $value !== null ? round((float) $value) : null)
                        ->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => '#ef4444',
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
