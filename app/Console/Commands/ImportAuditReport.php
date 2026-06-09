<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Services\AuditService;
use Illuminate\Console\Command;

class ImportAuditReport extends Command
{
    protected $signature = 'audit:import {websiteId} {filePath?}';
    protected $description = 'Importeer een Lighthouse report.json en sla het op als audit';

    public function handle(AuditService $auditService): int
    {
        $websiteId = $this->argument('websiteId');
        $filePath = $this->argument('filePath') ?? base_path('report.json');

        $website = Website::find($websiteId);

        if (! $website) {
            $this->error('Website niet gevonden.');
            return self::FAILURE;
        }

        try {
            $audit = $auditService->importFromJsonFile($website, $filePath);

            $this->info('Audit succesvol geïmporteerd.');
            $this->line('Website: ' . $website->name);
            $this->line('Performance: ' . ($audit->performance_score ?? '-'));
            $this->line('Accessibility: ' . ($audit->accessibility_score ?? '-'));
            $this->line('Best Practices: ' . ($audit->best_practices_score ?? '-'));
            $this->line('SEO: ' . ($audit->seo_score ?? '-'));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Fout bij importeren: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
