<?php

namespace App\Filament\Resources\Websites\Widgets;

use App\Models\Website;
use Filament\Widgets\ChartWidget;

class WebsiteClsHistoryChart extends ChartWidget
{
    public ?Website $record = null;

    protected ?string $heading = '3. Layout stability';

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
                    'label' => 'CLS',
                    'data' => $audits
                        ->pluck('cls')
                        ->map(fn ($value) => $value !== null ? (float) $value : null)
                        ->toArray(),
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
