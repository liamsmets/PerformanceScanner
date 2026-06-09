<?php

namespace App\Jobs;

use App\Models\WebsitePage;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunWebsitePageAuditScan implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $websitePageId
    ) {
    }

    public function handle(AuditService $auditService): void
    {
        $websitePage = WebsitePage::with('website')->find($this->websitePageId);

        if (! $websitePage) {
            return;
        }

        if (! $websitePage->is_active || ! $websitePage->website->is_active) {
            $websitePage->update([
                'is_scanning' => false,
            ]);

            return;
        }

        try {
            $auditService->runForWebsitePage($websitePage);
        } finally {
            $websitePage->update([
                'is_scanning' => false,
            ]);
        }
    }
}
