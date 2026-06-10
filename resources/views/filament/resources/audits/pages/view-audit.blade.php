<x-filament-panels::page>
    <x-history.styles />

    @php
        $audit = $this->record;
        $website = $audit->website;
        $websitePage = $audit->websitePage;

        $targetUrl = $websitePage?->url ?? $website?->url;
        $targetName = $websitePage?->name ?? $website?->name;
        $type = $websitePage ? 'Pagina' : 'Hoofdwebsite';
    @endphp

    <div class="ps-history">
        <div class="ps-header">
            <div class="ps-header-title">
                Audit van {{ $targetName }}
            </div>

            <div class="ps-muted">
                Website: {{ $website?->name }}
            </div>

            <div class="ps-muted">
                Type: {{ $type }}
            </div>

            @if ($targetUrl)
                <div class="ps-muted">
                    URL:
                    <a href="{{ $targetUrl }}" target="_blank" class="ps-link">
                        {{ $targetUrl }}
                    </a>
                </div>
            @endif

            <div class="ps-muted">
                Gescand op:
                {{ $audit->scanned_at?->format('d/m/Y H:i') ?? '-' }}
            </div>
        </div>

        <x-history.latest-audit
            :audit="$audit"
            title="Scanresultaat"
            description="Resultaat van deze specifieke Lighthouse-scan."
        />

        <x-history.improvement-suggestions :audit="$audit" />

        <x-history.metric-explanation />
    </div>
</x-filament-panels::page>
