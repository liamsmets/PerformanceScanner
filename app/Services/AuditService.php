<?php

namespace App\Services;

use App\Models\Audit;
use App\Models\Website;
use App\Models\WebsitePage;
use Carbon\Carbon;

class AuditService
{
    public function __construct(
        private LighthouseService $lighthouseService,
    ) {
    }

    public function importFromJsonFile(Website $website, string $filePath, ?WebsitePage $websitePage = null): Audit
    {
        if (! file_exists($filePath)) {
            throw new \Exception('Report bestand niet gevonden: ' . $filePath);
        }

        $json = file_get_contents($filePath);
        $report = json_decode($json, true);

        if (! is_array($report)) {
            throw new \Exception('Ongeldige JSON in report bestand.');
        }

        return Audit::create([
            'website_id' => $website->id,
            'website_page_id' => $websitePage?->id,
            'performance_score' => $this->extractScore($report, 'performance'),
            'accessibility_score' => $this->extractScore($report, 'accessibility'),
            'best_practices_score' => $this->extractScore($report, 'best-practices'),
            'seo_score' => $this->extractScore($report, 'seo'),
            'report_json' => $report,
            'scanned_at' => isset($report['fetchTime'])
                ? Carbon::parse($report['fetchTime'])
                : now(),
        ]);
    }

    public function runForWebsite(Website $website): Audit
    {
        return $this->runForUrl($website, $website->url);
    }

    public function runForWebsitePage(WebsitePage $websitePage): Audit
    {
        $websitePage->loadMissing('website');

        return $this->runForUrl(
            $websitePage->website,
            $websitePage->url,
            $websitePage
        );
    }

    private function runForUrl(Website $website, string $url, ?WebsitePage $websitePage = null): Audit
    {
        $scanResult = $this->lighthouseService->runAverage($url, 3);

        return Audit::create([
            'website_id' => $website->id,
            'website_page_id' => $websitePage?->id,
            'runs_used' => $scanResult['runs'],
            'performance_score' => $scanResult['performance_score'],
            'accessibility_score' => $scanResult['accessibility_score'],
            'best_practices_score' => $scanResult['best_practices_score'],
            'seo_score' => $scanResult['seo_score'],
            'lcp_ms' => $scanResult['lcp_ms'],
            'fcp_ms' => $scanResult['fcp_ms'],
            'tbt_ms' => $scanResult['tbt_ms'],
            'cls' => $scanResult['cls'],
            'report_json' => $scanResult['summary_json'],
            'scanned_at' => $scanResult['scanned_at'],
        ]);
    }

    private function extractScore(array $report, string $categoryKey): ?int
    {
        $score = $report['categories'][$categoryKey]['score'] ?? null;

        if ($score === null) {
            return null;
        }

        return (int) round($score * 100);
    }
}
