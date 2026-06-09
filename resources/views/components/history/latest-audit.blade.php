@props(['audit'])

@php
    $scoreStatus = function ($score) {
        if ($score === null) {
            return 'unknown';
        }

        $score = (int) $score;

        if ($score >= 90) {
            return 'good';
        }

        if ($score >= 50) {
            return 'warning';
        }

        return 'bad';
    };

    $metricStatus = function ($value, string $type) {
        if ($value === null) {
            return 'unknown';
        }

        $value = (float) $value;

        return match ($type) {
            'lcp' => $value <= 2500 ? 'good' : ($value <= 4000 ? 'warning' : 'bad'),
            'fcp' => $value <= 1800 ? 'good' : ($value <= 3000 ? 'warning' : 'bad'),
            'tbt' => $value <= 200 ? 'good' : ($value <= 600 ? 'warning' : 'bad'),
            'cls' => $value <= 0.1 ? 'good' : ($value <= 0.25 ? 'warning' : 'bad'),
            default => 'unknown',
        };
    };

    $formatMs = fn ($value) => $value !== null
        ? number_format((float) $value, 0, ',', '.') . ' ms'
        : '-';

    $formatCls = fn ($value) => $value !== null
        ? number_format((float) $value, 3, ',', '.')
        : '-';
@endphp

<div class="ps-card">
    <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem;">
        <div>
            <div class="ps-section-title">Laatste scan</div>
            <div class="ps-section-description">
                De meest recente Lighthouse-meting voor deze historiek.
            </div>
        </div>

        <div class="ps-muted">
            {{ $audit->scanned_at?->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="ps-section-title">1. Lighthouse scores</div>
    <div class="ps-section-description">
        Deze scores worden uitgedrukt op 100. Hoe hoger, hoe beter.
    </div>

    <div class="ps-grid-4">
        <div class="ps-metric-card ps-metric-card--{{ $scoreStatus($audit->performance_score) }}">
            <div class="ps-metric-label">Performance</div>
            <div class="ps-metric-value">{{ $audit->performance_score ?? '-' }}</div>
            <div class="ps-metric-help">Algemene snelheid en laadervaring.</div>
        </div>

        <div class="ps-metric-card ps-metric-card--{{ $scoreStatus($audit->accessibility_score) }}">
            <div class="ps-metric-label">Accessibility</div>
            <div class="ps-metric-value">{{ $audit->accessibility_score ?? '-' }}</div>
            <div class="ps-metric-help">Toegankelijkheid voor gebruikers.</div>
        </div>

        <div class="ps-metric-card ps-metric-card--{{ $scoreStatus($audit->best_practices_score) }}">
            <div class="ps-metric-label">Best Practices</div>
            <div class="ps-metric-value">{{ $audit->best_practices_score ?? '-' }}</div>
            <div class="ps-metric-help">Technische kwaliteit en veiligheid.</div>
        </div>

        <div class="ps-metric-card ps-metric-card--{{ $scoreStatus($audit->seo_score) }}">
            <div class="ps-metric-label">SEO</div>
            <div class="ps-metric-value">{{ $audit->seo_score ?? '-' }}</div>
            <div class="ps-metric-help">Basis voor zoekmachinevindbaarheid.</div>
        </div>
    </div>

    <div style="height: 1.5rem;"></div>

    <div class="ps-section-title">2. Laadtijden / blocking time</div>
    <div class="ps-section-description">
        Deze waarden worden gemeten in milliseconden. Hoe lager, hoe beter.
    </div>

    <div class="ps-grid-3">
        <div class="ps-metric-card ps-metric-card--{{ $metricStatus($audit->lcp_ms, 'lcp') }}">
            <div class="ps-metric-label">LCP</div>
            <div class="ps-metric-value">{{ $formatMs($audit->lcp_ms) }}</div>
            <div class="ps-metric-help">Tijd tot het grootste zichtbare element geladen is.</div>
        </div>

        <div class="ps-metric-card ps-metric-card--{{ $metricStatus($audit->fcp_ms, 'fcp') }}">
            <div class="ps-metric-label">FCP</div>
            <div class="ps-metric-value">{{ $formatMs($audit->fcp_ms) }}</div>
            <div class="ps-metric-help">Tijd tot de eerste inhoud zichtbaar wordt.</div>
        </div>

        <div class="ps-metric-card ps-metric-card--{{ $metricStatus($audit->tbt_ms, 'tbt') }}">
            <div class="ps-metric-label">TBT</div>
            <div class="ps-metric-value">{{ $formatMs($audit->tbt_ms) }}</div>
            <div class="ps-metric-help">Tijd waarin de pagina niet vlot reageert.</div>
        </div>
    </div>

    <div style="height: 1.5rem;"></div>

    <div class="ps-section-title">3. Layout stability</div>
    <div class="ps-section-description">
        CLS meet onverwachte verschuivingen tijdens het laden. Hoe lager, hoe beter.
    </div>

    <div class="ps-grid-2">
        <div class="ps-metric-card ps-metric-card--{{ $metricStatus($audit->cls, 'cls') }}">
            <div class="ps-metric-label">CLS</div>
            <div class="ps-metric-value">{{ $formatCls($audit->cls) }}</div>
            <div class="ps-metric-help">Meet onverwachte layoutverschuivingen.</div>
        </div>

        <div class="ps-metric-card ps-metric-card--unknown">
            <div class="ps-metric-label">Runs gebruikt</div>
            <div class="ps-metric-value">{{ $audit->runs_used ?? '-' }}</div>
            <div class="ps-metric-help">Aantal Lighthouse-runs voor het gemiddelde.</div>
        </div>
    </div>
</div>
