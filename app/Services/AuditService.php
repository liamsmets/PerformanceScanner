<?php

namespace App\Services;

use App\Models\Audit;
use App\Models\Website;
use App\Models\WebsitePage;
use Carbon\Carbon;
use Illuminate\Support\Str;

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

        $performanceScore = $this->extractScore($report, 'performance');
        $accessibilityScore = $this->extractScore($report, 'accessibility');
        $bestPracticesScore = $this->extractScore($report, 'best-practices');
        $seoScore = $this->extractScore($report, 'seo');

        $lcp = $this->extractNumericAuditValue($report, 'largest-contentful-paint');
        $fcp = $this->extractNumericAuditValue($report, 'first-contentful-paint');
        $tbt = $this->extractNumericAuditValue($report, 'total-blocking-time');
        $cls = $this->extractNumericAuditValue($report, 'cumulative-layout-shift');

        return Audit::create([
            'website_id' => $website->id,
            'website_page_id' => $websitePage?->id,
            'runs_used' => 1,
            'performance_score' => $performanceScore,
            'accessibility_score' => $accessibilityScore,
            'best_practices_score' => $bestPracticesScore,
            'seo_score' => $seoScore,
            'lcp_ms' => $lcp,
            'fcp_ms' => $fcp,
            'tbt_ms' => $tbt,
            'cls' => $cls,
            'report_json' => [
                'url' => $report['finalDisplayedUrl']
                    ?? $report['finalUrl']
                        ?? $report['requestedUrl']
                        ?? $websitePage?->url
                        ?? $website->url,
                'runs' => 1,
                'type' => 'imported_lighthouse_report',
                'metrics' => [
                    'cls' => $cls,
                    'fcp_ms' => $fcp,
                    'lcp_ms' => $lcp,
                    'tbt_ms' => $tbt,
                ],
                'averages' => [
                    'seo' => $seoScore,
                    'performance' => $performanceScore,
                    'accessibility' => $accessibilityScore,
                    'best-practices' => $bestPracticesScore,
                ],
                'improvements' => $this->extractImprovements($report),
            ],
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

        $summaryJson = is_array($scanResult['summary_json'] ?? null)
            ? $scanResult['summary_json']
            : [];

        $reportForImprovements = $this->pickReportForImprovements(
            $this->getReportsFromScanResult($scanResult)
        );

        $summaryJson['improvements'] = $this->extractImprovements($reportForImprovements);

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
            'report_json' => $summaryJson,
            'scanned_at' => $scanResult['scanned_at'],
        ]);
    }

    private function extractScore(array $report, string $categoryKey): ?int
    {
        if (isset($report['lighthouseResult'])) {
            $report = $report['lighthouseResult'];
        }

        $score = $report['categories'][$categoryKey]['score'] ?? null;

        if ($score === null) {
            return null;
        }

        return (int) round($score * 100);
    }

    private function extractNumericAuditValue(array $report, string $auditKey): ?float
    {
        if (isset($report['lighthouseResult'])) {
            $report = $report['lighthouseResult'];
        }

        $value = $report['audits'][$auditKey]['numericValue'] ?? null;

        if ($value === null) {
            return null;
        }

        return (float) $value;
    }

    private function getReportsFromScanResult(array $scanResult): array
    {
        if (isset($scanResult['reports']) && is_array($scanResult['reports'])) {
            return $scanResult['reports'];
        }

        if (isset($scanResult['lighthouse_reports']) && is_array($scanResult['lighthouse_reports'])) {
            return $scanResult['lighthouse_reports'];
        }

        if (isset($scanResult['raw_reports']) && is_array($scanResult['raw_reports'])) {
            return $scanResult['raw_reports'];
        }

        if (isset($scanResult['report']) && is_array($scanResult['report'])) {
            return [$scanResult['report']];
        }

        return [];
    }

    private function pickReportForImprovements(array $reports): array
    {
        if (empty($reports)) {
            return [];
        }

        $usableReports = collect($reports)
            ->map(function (array $report): array {
                if (isset($report['lighthouseResult']) && is_array($report['lighthouseResult'])) {
                    return $report['lighthouseResult'];
                }

                return $report;
            })
            ->filter(fn (array $report): bool => isset($report['categories'], $report['audits']));

        if ($usableReports->isEmpty()) {
            return [];
        }

        return $usableReports
            ->sortBy(fn (array $report): float => $report['categories']['performance']['score'] ?? 1)
            ->first();
    }

    private function extractImprovements(array $report): array
    {
        if (empty($report)) {
            return [];
        }

        if (isset($report['lighthouseResult'])) {
            $report = $report['lighthouseResult'];
        }

        if (! isset($report['categories'], $report['audits'])) {
            return [];
        }

        $categories = [
            'performance' => 'Performance',
            'accessibility' => 'Accessibility',
            'best-practices' => 'Best Practices',
            'seo' => 'SEO',
        ];

        $improvements = [];

        foreach ($categories as $categoryKey => $categoryLabel) {
            $auditRefs = $report['categories'][$categoryKey]['auditRefs'] ?? [];

            foreach ($auditRefs as $auditRef) {
                $auditId = $auditRef['id'] ?? null;

                if (! $auditId) {
                    continue;
                }

                $lighthouseAudit = $report['audits'][$auditId] ?? null;

                if (! $lighthouseAudit) {
                    continue;
                }

                $score = $lighthouseAudit['score'] ?? null;
                $scoreDisplayMode = $lighthouseAudit['scoreDisplayMode'] ?? null;

                if ($score === null || $score >= 1) {
                    continue;
                }

                if (in_array($scoreDisplayMode, ['notApplicable', 'manual', 'informative'], true)) {
                    continue;
                }

                $title = $lighthouseAudit['title'] ?? null;

                if (! $title) {
                    continue;
                }

                $improvements[$categoryLabel][] = [
                    'title' => Str::limit((string) $title, 150),
                    'description' => $this->cleanLighthouseText($lighthouseAudit['description'] ?? null),
                    'displayValue' => isset($lighthouseAudit['displayValue'])
                        ? Str::limit((string) $lighthouseAudit['displayValue'], 150)
                        : null,
                    'score' => $score,
                ];
            }

            if (isset($improvements[$categoryLabel])) {
                usort(
                    $improvements[$categoryLabel],
                    fn (array $a, array $b): int => $a['score'] <=> $b['score']
                );

                $improvements[$categoryLabel] = array_slice($improvements[$categoryLabel], 0, 5);
            }
        }

        return $improvements;
    }

    private function cleanLighthouseText(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        $text = strip_tags($text);
        $text = preg_replace('/\[(.*?)]\((.*?)\)/', '$1', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
        $text = preg_replace('/\s+/', ' ', $text);

        return Str::limit(trim($text), 300);
    }
}
