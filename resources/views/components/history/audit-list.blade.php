@props(['audits'])

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
    <div class="ps-section-title">Alle scans</div>

    <div class="ps-section-description">
        Elke rij toont één scanmoment. De kleuren helpen om snel te zien welke waarden goed zijn en welke aandacht nodig hebben.
        Bij veel scans kan je binnen deze tabel scrollen.
    </div>

    <div class="ps-table-wrapper" style="max-height: 560px; overflow-y: auto; overflow-x: auto;">
        <table class="ps-table">
            <thead>
            <tr>
                <th style="position: sticky; top: 0; background: #18181b; z-index: 2;">
                    Scanmoment
                </th>

                <th style="position: sticky; top: 0; background: #18181b; z-index: 2;">
                    Perf.
                    <span class="ps-th-small">Performance</span>
                </th>

                <th style="position: sticky; top: 0; background: #18181b; z-index: 2;">
                    Access.
                    <span class="ps-th-small">Accessibility</span>
                </th>

                <th style="position: sticky; top: 0; background: #18181b; z-index: 2;">
                    Best
                    <span class="ps-th-small">Best Practices</span>
                </th>

                <th style="position: sticky; top: 0; background: #18181b; z-index: 2;">
                    SEO
                    <span class="ps-th-small">Vindbaarheid</span>
                </th>

                <th style="position: sticky; top: 0; background: #18181b; z-index: 2;">
                    LCP
                    <span class="ps-th-small">Grootste element</span>
                </th>

                <th style="position: sticky; top: 0; background: #18181b; z-index: 2;">
                    FCP
                    <span class="ps-th-small">Eerste inhoud</span>
                </th>

                <th style="position: sticky; top: 0; background: #18181b; z-index: 2;">
                    TBT
                    <span class="ps-th-small">Blokkering</span>
                </th>

                <th style="position: sticky; top: 0; background: #18181b; z-index: 2;">
                    CLS
                    <span class="ps-th-small">Verschuiving</span>
                </th>

                <th style="position: sticky; top: 0; background: #18181b; z-index: 2;">
                    Runs
                    <span class="ps-th-small">Gemiddelde</span>
                </th>
            </tr>
            </thead>

            <tbody>
            @forelse ($audits as $audit)
                <tr>
                    <td>
                        {{ $audit->scanned_at?->format('d/m/Y H:i') }}
                    </td>

                    <td>
                            <span class="ps-badge ps-badge--{{ $scoreStatus($audit->performance_score) }}">
                                {{ $audit->performance_score ?? '-' }}
                            </span>
                    </td>

                    <td>
                            <span class="ps-badge ps-badge--{{ $scoreStatus($audit->accessibility_score) }}">
                                {{ $audit->accessibility_score ?? '-' }}
                            </span>
                    </td>

                    <td>
                            <span class="ps-badge ps-badge--{{ $scoreStatus($audit->best_practices_score) }}">
                                {{ $audit->best_practices_score ?? '-' }}
                            </span>
                    </td>

                    <td>
                            <span class="ps-badge ps-badge--{{ $scoreStatus($audit->seo_score) }}">
                                {{ $audit->seo_score ?? '-' }}
                            </span>
                    </td>

                    <td>
                            <span class="ps-badge ps-badge--{{ $metricStatus($audit->lcp_ms, 'lcp') }}">
                                {{ $formatMs($audit->lcp_ms) }}
                            </span>
                    </td>

                    <td>
                            <span class="ps-badge ps-badge--{{ $metricStatus($audit->fcp_ms, 'fcp') }}">
                                {{ $formatMs($audit->fcp_ms) }}
                            </span>
                    </td>

                    <td>
                            <span class="ps-badge ps-badge--{{ $metricStatus($audit->tbt_ms, 'tbt') }}">
                                {{ $formatMs($audit->tbt_ms) }}
                            </span>
                    </td>

                    <td>
                            <span class="ps-badge ps-badge--{{ $metricStatus($audit->cls, 'cls') }}">
                                {{ $formatCls($audit->cls) }}
                            </span>
                    </td>

                    <td>
                            <span class="ps-badge ps-badge--unknown">
                                {{ $audit->runs_used ?? '-' }}
                            </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: #9ca3af; padding: 1.5rem;">
                        Er zijn nog geen audits beschikbaar.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
