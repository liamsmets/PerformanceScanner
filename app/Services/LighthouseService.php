<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class LighthouseService
{
    public function runAverage(string $url, int $runs = 3): array
    {
        $results = [];

        for ($i = 0; $i < $runs; $i++) {
            $results[] = $this->runSingle($url);
        }

        $performanceScore = $this->averageCategoryScore($results, 'performance');
        $accessibilityScore = $this->averageCategoryScore($results, 'accessibility');
        $bestPracticesScore = $this->averageCategoryScore($results, 'best-practices');
        $seoScore = $this->averageCategoryScore($results, 'seo');

        $lcp = $this->averageAuditMetric($results, 'largest-contentful-paint', 0);
        $fcp = $this->averageAuditMetric($results, 'first-contentful-paint', 0);
        $tbt = $this->averageAuditMetric($results, 'total-blocking-time', 0);
        $cls = $this->averageAuditMetric($results, 'cumulative-layout-shift', 3);

        return [
            'url' => $url,
            'runs' => $runs,
            'performance_score' => $performanceScore,
            'accessibility_score' => $accessibilityScore,
            'best_practices_score' => $bestPracticesScore,
            'seo_score' => $seoScore,
            'lcp_ms' => $lcp,
            'fcp_ms' => $fcp,
            'tbt_ms' => $tbt,
            'cls' => $cls,
            'scanned_at' => now(),

            // Belangrijk: deze volledige reports worden NIET opgeslagen in de database.
            // Ze worden alleen tijdelijk gebruikt door AuditService om verbeterpunten uit te halen.
            'reports' => $results,

            'summary_json' => [
                'type' => 'average_of_multiple_runs',
                'runs' => $runs,
                'url' => $url,
                'averages' => [
                    'performance' => $performanceScore,
                    'accessibility' => $accessibilityScore,
                    'best-practices' => $bestPracticesScore,
                    'seo' => $seoScore,
                ],
                'metrics' => [
                    'lcp_ms' => $lcp,
                    'fcp_ms' => $fcp,
                    'tbt_ms' => $tbt,
                    'cls' => $cls,
                ],
            ],
        ];
    }

    private function runSingle(string $url): array
    {
        $fileName = 'lighthouse-' . uniqid() . '.json';
        $outputPath = storage_path('app/' . $fileName);

        $npxCommand = PHP_OS_FAMILY === 'Windows' ? 'npx.cmd' : 'npx';

        $result = Process::timeout(300)->path(base_path())->run([
            $npxCommand,
            'lighthouse',
            $url,
            '--output=json',
            '--output-path=' . $outputPath,
            '--quiet',
            '--chrome-flags=--headless',
            '--only-categories=performance,accessibility,best-practices,seo',
        ]);

        if ($result->failed()) {
            throw new \Exception('Lighthouse scan mislukt: ' . $result->errorOutput());
        }

        if (! File::exists($outputPath)) {
            throw new \Exception('Lighthouse heeft geen outputbestand aangemaakt.');
        }

        $json = File::get($outputPath);
        $report = json_decode($json, true);

        File::delete($outputPath);

        if (! is_array($report)) {
            throw new \Exception('Ongeldige JSON ontvangen van Lighthouse.');
        }

        return $report;
    }

    private function averageCategoryScore(array $results, string $categoryKey): ?int
    {
        $scores = [];

        foreach ($results as $report) {
            $score = $report['categories'][$categoryKey]['score'] ?? null;

            if ($score !== null) {
                $scores[] = $score * 100;
            }
        }

        if ($scores === []) {
            return null;
        }

        return (int) round(array_sum($scores) / count($scores));
    }

    private function averageAuditMetric(array $results, string $auditKey, int $precision = 0): int|float|null
    {
        $values = [];

        foreach ($results as $report) {
            $value = $report['audits'][$auditKey]['numericValue'] ?? null;

            if (is_numeric($value)) {
                $values[] = (float) $value;
            }
        }

        if ($values === []) {
            return null;
        }

        return round(array_sum($values) / count($values), $precision);
    }
}
