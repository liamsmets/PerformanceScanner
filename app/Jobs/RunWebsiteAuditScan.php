<?php

namespace App\Jobs;

use App\Models\Website;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;

#[Timeout(900)]
#[Tries(1)]
class RunWebsiteAuditScan implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $websiteId,
    ) {
    }

    public function handle(AuditService $auditService): void
    {
        $website = Website::find($this->websiteId);

        if (! $website || ! $website->is_active) {
            return;
        }

        try {
            $auditService->runForWebsite($website);
        } finally {
            $website->update([
                'is_scanning' => false,
            ]);
        }
    }
}
