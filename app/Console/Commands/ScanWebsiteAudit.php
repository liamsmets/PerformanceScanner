<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Services\AuditService;
use Illuminate\Console\Command;

class ScanWebsiteAudit extends Command
{
    protected $signature = 'audit:scan {websiteId}';
    protected $description = 'Voer een live Lighthouse scan uit voor een website en sla de audit op';

    public function handle(AuditService $auditService): int
    {
        $websiteId = $this->argument('websiteId');

        $website = Website::find($websiteId);

        if (! $website) {
            $this->error('Website niet gevonden.');
            return self::FAILURE;
        }

        try {
            $this->info('Scan gestart voor: ' . $website->name . ' (' . $website->url . ')');

            $audit = $auditService->runForWebsite($website);

            $this->info('Audit succesvol opgeslagen.');
            $this->line('Performance: ' . ($audit->performance_score ?? '-'));
            $this->line('Accessibility: ' . ($audit->accessibility_score ?? '-'));
            $this->line('Best Practices: ' . ($audit->best_practices_score ?? '-'));
            $this->line('SEO: ' . ($audit->seo_score ?? '-'));
            $this->line('Gescand op: ' . ($audit->scanned_at?->format('d/m/Y H:i:s') ?? '-'));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Fout bij scannen: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
