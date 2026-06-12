<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebsitePageDiscoveryService
{
    private const int MAX_PAGES = 100;

    public function discoverUrls(Website $website): array
    {
        $parts = parse_url($website->url);

        if (! isset($parts['scheme'], $parts['host'])) {
            return [];
        }

        $rootUrl = $parts['scheme'] . '://' . $parts['host'];
        $websiteHost = $this->cleanHost($parts['host']);

        if ($websiteHost === null) {
            return [];
        }

        $urls = $this->discoverFromSitemaps($rootUrl, $websiteHost);

        if (empty($urls)) {
            $urls = $this->discoverFromHomepageLinks($rootUrl, $websiteHost);
        }

        return array_slice(array_values(array_unique($urls)), 0, self::MAX_PAGES);
    }

    public function makeNameFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        if ($path === '/') {
            return 'Home';
        }

        $lastPart = basename($path);

        return Str::of($lastPart)
            ->replace(['-', '_'], ' ')
            ->title()
            ->toString();
    }

    public function normalizeForComparison(string $url): string
    {
        $url = trim($url);
        $url = strtok($url, '#');

        $parts = parse_url($url);

        if (! isset($parts['host'])) {
            return rtrim(strtolower($url), '/');
        }

        $scheme = $parts['scheme'] ?? 'https';
        $host = $this->cleanHost($parts['host']);
        $path = $parts['path'] ?? '/';

        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/');

        if ($path === '') {
            $path = '/';
        }

        return strtolower($scheme . '://' . $host . ($path === '/' ? '' : $path));
    }

    private function discoverFromSitemaps(string $rootUrl, string $websiteHost): array
    {
        $sitemapUrls = $this->getSitemapUrls($rootUrl, $websiteHost);

        $urls = [];

        foreach ($sitemapUrls as $sitemapUrl) {
            $urls = array_merge(
                $urls,
                $this->readSitemap($sitemapUrl, $websiteHost)
            );

            if (count($urls) >= self::MAX_PAGES) {
                break;
            }
        }

        return $urls;
    }

    private function discoverFromHomepageLinks(string $rootUrl, string $websiteHost): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 PerformanceScanner',
            ])
                ->timeout(15)
                ->get($rootUrl);
        } catch (\Exception) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $html = $response->body();

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $loaded = $dom->loadHTML($html);

        libxml_clear_errors();

        if (! $loaded) {
            return [];
        }

        $links = $dom->getElementsByTagName('a');
        $urls = [];

        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            $url = $this->makeAbsoluteUrl($href, $rootUrl);
            $url = $this->normalizePageUrl($url, $websiteHost);

            if ($url !== null) {
                $urls[] = $url;
            }

            if (count($urls) >= self::MAX_PAGES) {
                break;
            }
        }

        return $urls;
    }

    private function getSitemapUrls(string $rootUrl, string $websiteHost): array
    {
        $sitemapUrls = [];

        $sitemapUrls = array_merge(
            $sitemapUrls,
            $this->getSitemapUrlsFromRobotsTxt($rootUrl, $websiteHost)
        );

        $sitemapUrls = array_merge($sitemapUrls, [
            $rootUrl . '/sitemap.xml',
            $rootUrl . '/sitemap_index.xml',
            $rootUrl . '/sitemap-index.xml',
            $rootUrl . '/wp-sitemap.xml',
        ]);

        return array_values(array_unique($sitemapUrls));
    }

    private function getSitemapUrlsFromRobotsTxt(string $rootUrl, string $websiteHost): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 PerformanceScanner',
            ])
                ->timeout(10)
                ->get($rootUrl . '/robots.txt');
        } catch (\Exception) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $sitemapUrls = [];
        $lines = explode("\n", $response->body());

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (Str::startsWith($line, '#')) {
                continue;
            }

            if (! Str::startsWith(Str::lower($line), 'sitemap:')) {
                continue;
            }

            $sitemapUrl = trim(Str::after($line, ':'));
            $sitemapUrl = $this->normalizeSitemapUrl($sitemapUrl, $rootUrl, $websiteHost);

            if ($sitemapUrl !== null) {
                $sitemapUrls[] = $sitemapUrl;
            }
        }

        return $sitemapUrls;
    }

    private function normalizeSitemapUrl(string $sitemapUrl, string $rootUrl, string $websiteHost): ?string
    {
        $sitemapUrl = trim($sitemapUrl);

        if ($sitemapUrl === '') {
            return null;
        }

        if (Str::startsWith($sitemapUrl, '/')) {
            $sitemapUrl = $rootUrl . $sitemapUrl;
        }

        if (! Str::startsWith($sitemapUrl, ['https://'])) {
            $sitemapUrl = $rootUrl . '/' . ltrim($sitemapUrl, '/');
        }

        $parts = parse_url($sitemapUrl);

        if (! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $sitemapHost = $this->cleanHost($parts['host']);

        if ($sitemapHost !== $websiteHost) {
            return null;
        }

        return $sitemapUrl;
    }

    private function readSitemap(string $sitemapUrl, string $websiteHost, int $depth = 0): array
    {
        if ($depth > 2) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 PerformanceScanner',
            ])
                ->timeout(15)
                ->get($sitemapUrl);
        } catch (\Exception) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $xml = @simplexml_load_string($response->body());

        if (! $xml) {
            return [];
        }

        $urls = [];
        $rootName = strtolower($xml->getName());

        if ($rootName === 'urlset') {
            foreach ($xml->url as $urlNode) {
                $url = $this->normalizePageUrl((string) $urlNode->loc, $websiteHost);

                if ($url !== null) {
                    $urls[] = $url;
                }

                if (count($urls) >= self::MAX_PAGES) {
                    break;
                }
            }
        }

        if ($rootName === 'sitemapindex') {
            foreach ($xml->sitemap as $sitemapNode) {
                $childSitemapUrl = $this->normalizeSitemapUrl(
                    (string) $sitemapNode->loc,
                    $sitemapUrl,
                    $websiteHost
                );

                if ($childSitemapUrl === null) {
                    continue;
                }

                $urls = array_merge(
                    $urls,
                    $this->readSitemap($childSitemapUrl, $websiteHost, $depth + 1)
                );

                if (count($urls) >= self::MAX_PAGES) {
                    break;
                }
            }
        }

        return $urls;
    }

    private function makeAbsoluteUrl(string $href, string $rootUrl): string
    {
        $href = trim($href);

        if ($href === '') {
            return '';
        }

        if (Str::startsWith($href, ['#', 'mailto:', 'tel:', 'javascript:'])) {
            return '';
        }

        if (Str::startsWith($href, ['https://'])) {
            return $href;
        }

        if (Str::startsWith($href, '//')) {
            return 'https:' . $href;
        }

        if (Str::startsWith($href, '/')) {
            return $rootUrl . $href;
        }

        return $rootUrl . '/' . ltrim($href, '/');
    }

    private function normalizePageUrl(string $url, string $websiteHost): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $url = strtok($url, '#');

        $parts = parse_url($url);

        if (! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $urlHost = $this->cleanHost($parts['host']);

        if ($urlHost !== $websiteHost) {
            return null;
        }

        $path = $parts['path'] ?? '/';

        if ($this->isUnsupportedFile($path)) {
            return null;
        }

        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/');

        if ($path === '') {
            $path = '/';
        }

        return $parts['scheme'] . '://' . $parts['host'] . ($path === '/' ? '' : $path);
    }

    private function cleanHost(?string $host): ?string
    {
        if ($host === null) {
            return null;
        }

        return Str::of($host)
            ->lower()
            ->replaceStart('www.', '')
            ->toString();
    }

    private function isUnsupportedFile(string $path): bool
    {
        return Str::endsWith(strtolower($path), [
            '.pdf',
            '.jpg',
            '.jpeg',
            '.png',
            '.gif',
            '.webp',
            '.svg',
            '.css',
            '.js',
            '.zip',
        ]);
    }
}
