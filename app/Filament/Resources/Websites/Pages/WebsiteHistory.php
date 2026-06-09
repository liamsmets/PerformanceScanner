<?php

namespace App\Filament\Resources\Websites\Pages;

use App\Filament\Resources\Websites\WebsiteResource;
use App\Filament\Resources\Websites\Widgets\WebsiteClsHistoryChart;
use App\Filament\Resources\Websites\Widgets\WebsiteCoreWebVitalsHistoryChart;
use App\Filament\Resources\Websites\Widgets\WebsiteScoresHistoryChart;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class WebsiteHistory extends Page
{
    use InteractsWithRecord;

    protected static string $resource = WebsiteResource::class;

    protected string $view = 'filament.resources.shared.history';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
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
        return null;
    }

    public function getHistoryUrl(): string
    {
        return $this->getRecord()->url;
    }

    public function getLatestAudit()
    {
        return $this->getRecord()
            ->audits()
            ->whereNull('website_page_id')
            ->latest('scanned_at')
            ->first();
    }

    public function getAudits()
    {
        return $this->getRecord()
            ->audits()
            ->whereNull('website_page_id')
            ->latest('scanned_at')
            ->get();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WebsiteScoresHistoryChart::make([
                'record' => $this->getRecord(),
            ]),

            WebsiteCoreWebVitalsHistoryChart::make([
                'record' => $this->getRecord(),
            ]),

            WebsiteClsHistoryChart::make([
                'record' => $this->getRecord(),
            ]),
        ];
    }
}
