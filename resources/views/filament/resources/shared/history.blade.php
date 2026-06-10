<x-filament-panels::page>
    <x-history.styles />

    @php
        $latestAudit = $this->getLatestAudit();
        $audits = $this->getAudits();
    @endphp

    <div class="ps-history">
        <div class="ps-header">
            <div class="ps-header-title">
                Historiek van {{ $this->getHistoryTitle() }}
            </div>

            @if ($this->getHistorySubtitle())
                <div class="ps-muted">
                    {{ $this->getHistorySubtitle() }}
                </div>
            @endif

            <div class="ps-muted">
                URL:
                <a href="{{ $this->getHistoryUrl() }}" target="_blank" class="ps-link">
                    {{ $this->getHistoryUrl() }}
                </a>
            </div>

            <div class="ps-muted">
                Aantal audits: {{ $audits->count() }}
            </div>
        </div>

        @if ($latestAudit)
            <x-history.latest-audit :audit="$latestAudit" />
            <x-history.improvement-suggestions :audit="$latestAudit" />
        @endif

        <x-history.audit-list :audits="$audits" />

        <x-history.metric-explanation />
    </div>
</x-filament-panels::page>
