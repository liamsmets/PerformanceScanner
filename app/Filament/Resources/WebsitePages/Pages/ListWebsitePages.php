<?php

namespace App\Filament\Resources\WebsitePages\Pages;

use App\Filament\Resources\WebsitePages\WebsitePageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWebsitePages extends ListRecords
{
    protected static string $resource = WebsitePageResource::class;

    public ?int $websiteId = null;

    public function mount(): void
    {
        parent::mount();

        $websiteId = request()->query('website_id');

        $this->websiteId = is_numeric($websiteId)
            ? (int) $websiteId
            : null;
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ->when($this->websiteId, function (Builder $query): Builder {
                return $query->where('website_id', $this->websiteId);
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
