<?php

namespace App\Filament\Resources\WebsitePages\Pages;

use App\Filament\Resources\WebsitePages\WebsitePageResource;
use App\Filament\Resources\WebsitePages\Widgets\WebsitePageClsHistoryChart;
use App\Filament\Resources\WebsitePages\Widgets\WebsitePageCoreWebVitalsHistoryChart;
use App\Filament\Resources\WebsitePages\Widgets\WebsitePageScoresHistoryChart;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class WebsitePageHistory extends Page
{
    use InteractsWithRecord;

    protected static string $resource = WebsitePageResource::class;

    protected string $view = 'filament.resources.shared.history';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->loadMissing('website');
    }

    public function getTitle(): string
    {
        return 'Historiek - ' . $this->getRecord()->name;
    }

    public function getHistoryTitle(): string
    {
        return $this->getRecord()->name;
    }

    public function getHistorySubtitle(): ?string
    {
        return 'Website: ' . $this->getRecord()->website->name;
    }

    public function getHistoryUrl(): string
    {
        return $this->getRecord()->url;
    }

    public function getLatestAudit()
    {
        return $this->getRecord()
            ->audits()
            ->latest('scanned_at')
            ->first();
    }

    public function getAudits()
    {
        return $this->getRecord()
            ->audits()
            ->latest('scanned_at')
            ->get();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WebsitePageScoresHistoryChart::make([
                'record' => $this->getRecord(),
            ]),

            WebsitePageCoreWebVitalsHistoryChart::make([
                'record' => $this->getRecord(),
            ]),

            WebsitePageClsHistoryChart::make([
                'record' => $this->getRecord(),
            ]),
        ];
    }
}
