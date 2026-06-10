@props(['audit'])

@php
    $report = $audit->report_json ?? [];

    $storedImprovements = $report['improvements'] ?? [];
    $usesStoredImprovements = ! empty($storedImprovements);

    $suggestions = $storedImprovements;

    $metrics = $report['metrics'] ?? [];
    $averages = $report['averages'] ?? [];

    $performanceScore = $audit->performance_score ?? ($averages['performance'] ?? null);
    $accessibilityScore = $audit->accessibility_score ?? ($averages['accessibility'] ?? null);
    $bestPracticesScore = $audit->best_practices_score ?? ($averages['best-practices'] ?? null);
    $seoScore = $audit->seo_score ?? ($averages['seo'] ?? null);

    $lcp = $audit->lcp_ms ?? ($metrics['lcp_ms'] ?? null);
    $fcp = $audit->fcp_ms ?? ($metrics['fcp_ms'] ?? null);
    $tbt = $audit->tbt_ms ?? ($metrics['tbt_ms'] ?? null);
    $cls = $audit->cls ?? ($metrics['cls'] ?? null);

    $formatMs = function ($value): string {
        if ($value === null) {
            return '-';
        }

        if ($value >= 1000) {
            return number_format($value / 1000, 1, ',', '.') . ' s';
        }

        return round($value) . ' ms';
    };

    $formatCls = function ($value): string {
        if ($value === null) {
            return '-';
        }

        return number_format((float) $value, 3, ',', '.');
    };

    $addSuggestion = function (string $category, string $title, string $description, ?string $value = null) use (&$suggestions): void {
        $suggestions[$category][] = [
            'title' => $title,
            'description' => $description,
            'value' => $value,
        ];
    };

    if (empty($suggestions)) {
        if ($performanceScore !== null && $performanceScore < 90) {
            if ($lcp !== null && $lcp > 4000) {
                $addSuggestion(
                    'Performance',
                    'Largest Contentful Paint is traag',
                    'Het grootste zichtbare element op de pagina laadt te laat. Dit heeft een grote invloed op de performance-score.',
                    'LCP: ' . $formatMs($lcp)
                );
            } elseif ($lcp !== null && $lcp > 2500) {
                $addSuggestion(
                    'Performance',
                    'Largest Contentful Paint kan beter',
                    'Het grootste zichtbare element laadt iets te traag. Een lagere LCP zorgt meestal voor een betere performance-score.',
                    'LCP: ' . $formatMs($lcp)
                );
            }

            if ($fcp !== null && $fcp > 3000) {
                $addSuggestion(
                    'Performance',
                    'First Contentful Paint is traag',
                    'De eerste zichtbare content verschijnt pas laat op het scherm. Hierdoor voelt de pagina traag aan voor de gebruiker.',
                    'FCP: ' . $formatMs($fcp)
                );
            } elseif ($fcp !== null && $fcp > 1800) {
                $addSuggestion(
                    'Performance',
                    'First Contentful Paint kan beter',
                    'De eerste content verschijnt niet heel snel. Dit kan de gebruikerservaring negatief beïnvloeden.',
                    'FCP: ' . $formatMs($fcp)
                );
            }

            if ($tbt !== null && $tbt > 600) {
                $addSuggestion(
                    'Performance',
                    'Total Blocking Time is hoog',
                    'De pagina blokkeert de browser te lang tijdens het laden. Vaak komt dit door zware JavaScript.',
                    'TBT: ' . $formatMs($tbt)
                );
            } elseif ($tbt !== null && $tbt > 200) {
                $addSuggestion(
                    'Performance',
                    'Total Blocking Time kan beter',
                    'Er is merkbare blocking time tijdens het laden. Minder of lichtere JavaScript kan hierbij helpen.',
                    'TBT: ' . $formatMs($tbt)
                );
            }

            if ($cls !== null && $cls > 0.25) {
                $addSuggestion(
                    'Performance',
                    'Veel layoutverschuivingen',
                    'Elementen verschuiven tijdens het laden van de pagina. Dit zorgt voor een minder stabiele gebruikerservaring.',
                    'CLS: ' . $formatCls($cls)
                );
            } elseif ($cls !== null && $cls > 0.1) {
                $addSuggestion(
                    'Performance',
                    'Layout stability kan beter',
                    'Er zijn enkele layoutverschuivingen tijdens het laden van de pagina.',
                    'CLS: ' . $formatCls($cls)
                );
            }

            if (empty($suggestions['Performance'])) {
                $addSuggestion(
                    'Performance',
                    'Performance-score is lager dan verwacht',
                    'De performance-score is lager dan 90. Er zijn geen concrete Lighthouse-verbeterpunten opgeslagen voor deze scan, maar laadtijd, JavaScript en afbeeldingen zijn meestal de eerste zaken om te controleren.',
                    'Score: ' . $performanceScore
                );
            }
        }

        if ($accessibilityScore !== null && $accessibilityScore < 90) {
            $addSuggestion(
                'Accessibility',
                'Accessibility-score is laag',
                'Controleer vooral alt-teksten bij afbeeldingen, kleurcontrast, formulierlabels, knopteksten en een logische headingstructuur.',
                'Score: ' . $accessibilityScore
            );
        }

        if ($bestPracticesScore !== null && $bestPracticesScore < 90) {
            $addSuggestion(
                'Best Practices',
                'Best Practices-score is laag',
                'Controleer mogelijke browserfouten, verouderde of onveilige technieken, HTTPS-instellingen en correcte afbeeldingsverhoudingen.',
                'Score: ' . $bestPracticesScore
            );
        }

        if ($seoScore !== null && $seoScore < 90) {
            $addSuggestion(
                'SEO',
                'SEO-score is laag',
                'Controleer onder andere de meta description, paginatitel, indexeerbaarheid, links en mobiele leesbaarheid.',
                'Score: ' . $seoScore
            );
        }
    }
@endphp

<div class="ps-card">
    <div class="ps-section-title">Belangrijkste verbeterpunten</div>

    <div class="ps-section-description">
        @if ($usesStoredImprovements)
            Deze punten komen rechtstreeks uit het Lighthouse-rapport van deze scan. Alleen de belangrijkste verbeterpunten worden opgeslagen, niet het volledige rapport.
        @else
            Deze punten zijn gebaseerd op de opgeslagen Lighthouse-scores en web vitals van deze scan.
        @endif
    </div>

    @if (empty($suggestions))
        <div class="ps-muted">
            Er zijn geen duidelijke verbeterpunten gevonden in deze scan. De belangrijkste scores en metrics liggen waarschijnlijk goed.
        </div>
    @else
        <div class="ps-grid-2">
            @foreach ($suggestions as $category => $items)
                @if (count($items) > 0)
                    <div class="ps-explanation-card">
                        <div class="ps-explanation-title">{{ $category }}</div>

                        <ul style="margin-top: 0.75rem; padding-left: 1.25rem;">
                            @foreach ($items as $item)
                                @php
                                    $value = $item['displayValue'] ?? $item['value'] ?? null;
                                    $description = $item['description'] ?? null;
                                @endphp

                                <li style="margin-bottom: 0.85rem;">
                                    <strong>{{ $item['title'] }}</strong>

                                    @if ($value)
                                        <div class="ps-muted">
                                            {{ $value }}
                                        </div>
                                    @endif

                                    @if ($description)
                                        <div class="ps-muted" style="margin-top: 0.25rem;">
                                            {{ $description }}
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
