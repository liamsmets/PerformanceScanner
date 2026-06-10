<?php

namespace App\Jobs;

use App\Models\ScanLog;
use App\Models\WebsitePage;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

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

        $scanLog = ScanLog::create([
            'website_id' => $websitePage->website_id,
            'website_page_id' => $websitePage->id,
            'target_url' => $websitePage->url,
            'status' => 'running',
            'started_at' => now(),
        ]);

        if (! $websitePage->is_active || ! $websitePage->website->is_active) {
            $scanLog->update([
                'status' => 'failed',
                'error_message' => 'De pagina of website is niet actief.',
                'finished_at' => now(),
            ]);

            $websitePage->update([
                'is_scanning' => false,
            ]);

            return;
        }

        try {
            $audit = $auditService->runForWebsitePage($websitePage);

            $scanLog->update([
                'audit_id' => $audit->id,
                'status' => 'success',
                'error_message' => null,
                'finished_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $scanLog->update([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 1000),
                'finished_at' => now(),
            ]);
        } finally {
            $websitePage->update([
                'is_scanning' => false,
            ]);
        }
    }
}
