<?php

namespace App\Filament\Resources\WebsitePages\Widgets;

use App\Models\WebsitePage;
use Filament\Widgets\ChartWidget;

class WebsitePageClsHistoryChart extends ChartWidget
{
    public ?WebsitePage $record = null;

    protected ?string $heading = '3. Layout stability';

    protected function getData(): array
    {
        $audits = $this->record
            ->audits()
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
