<?php

namespace App\Jobs;

use App\Models\ScanLog;
use App\Models\Website;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class RunWebsiteAuditScan implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $websiteId
    ) {
    }

    public function handle(AuditService $auditService): void
    {
        $website = Website::find($this->websiteId);

        if (! $website) {
            return;
        }

        $scanLog = ScanLog::create([
            'website_id' => $website->id,
            'website_page_id' => null,
            'target_url' => $website->url,
            'status' => 'running',
            'started_at' => now(),
        ]);

        if (! $website->is_active) {
            $scanLog->update([
                'status' => 'failed',
                'error_message' => 'De website is niet actief.',
                'finished_at' => now(),
            ]);

            $website->update([
                'is_scanning' => false,
            ]);

            return;
        }

        try {
            $audit = $auditService->runForWebsite($website);

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
            $website->update([
                'is_scanning' => false,
            ]);
        }
    }
}
