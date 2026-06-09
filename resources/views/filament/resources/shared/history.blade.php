<x-filament-panels::page>
    <style>
        .ps-history {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .ps-header {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .ps-header-title {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .ps-muted {
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .ps-link {
            color: #facc15;
            text-decoration: none;
            word-break: break-all;
        }

        .ps-link:hover {
            text-decoration: underline;
        }

        .ps-card {
            background: #18181b;
            border: 1px solid #3f3f46;
            border-radius: 0.9rem;
            padding: 1.25rem;
        }

        .ps-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .ps-section-description {
            color: #9ca3af;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .ps-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .ps-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .ps-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .ps-metric-card {
            border: 1px solid #3f3f46;
            border-radius: 0.75rem;
            padding: 1rem;
            background: #111113;
        }

        .ps-metric-card--good {
            border-color: rgba(34, 197, 94, 0.55);
            background: rgba(34, 197, 94, 0.08);
        }

        .ps-metric-card--warning {
            border-color: rgba(250, 204, 21, 0.55);
            background: rgba(250, 204, 21, 0.08);
        }

        .ps-metric-card--bad {
            border-color: rgba(239, 68, 68, 0.55);
            background: rgba(239, 68, 68, 0.08);
        }

        .ps-metric-card--unknown {
            border-color: #3f3f46;
            background: #111113;
        }

        .ps-metric-label {
            color: #a1a1aa;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .ps-metric-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .ps-metric-card--good .ps-metric-value {
            color: #22c55e;
        }

        .ps-metric-card--warning .ps-metric-value {
            color: #facc15;
        }

        .ps-metric-card--bad .ps-metric-value {
            color: #ef4444;
        }

        .ps-metric-card--unknown .ps-metric-value {
            color: #d4d4d8;
        }

        .ps-metric-help {
            color: #a1a1aa;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            line-height: 1.35;
        }

        .ps-table-wrapper {
            overflow-x: auto;
        }

        .ps-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .ps-table th {
            color: #a1a1aa;
            font-weight: 600;
            text-align: left;
            border-bottom: 1px solid #3f3f46;
            padding: 0.75rem;
            white-space: nowrap;
        }

        .ps-table td {
            border-bottom: 1px solid #27272a;
            padding: 0.75rem;
            white-space: nowrap;
        }

        .ps-th-small {
            display: block;
            color: #71717a;
            font-size: 0.72rem;
            font-weight: 400;
            margin-top: 0.15rem;
        }

        .ps-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.3rem;
            border-radius: 999px;
            padding: 0.25rem 0.55rem;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .ps-badge--good {
            color: #22c55e;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.4);
        }

        .ps-badge--warning {
            color: #facc15;
            background: rgba(250, 204, 21, 0.12);
            border: 1px solid rgba(250, 204, 21, 0.4);
        }

        .ps-badge--bad {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.4);
        }

        .ps-badge--unknown {
            color: #d4d4d8;
            background: rgba(113, 113, 122, 0.18);
            border: 1px solid rgba(113, 113, 122, 0.4);
        }

        .ps-explanation-card {
            border: 1px solid #3f3f46;
            border-radius: 0.75rem;
            padding: 1rem;
            background: #111113;
        }

        .ps-explanation-title {
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .ps-explanation-text {
            color: #a1a1aa;
            font-size: 0.88rem;
            line-height: 1.45;
        }

        @media (max-width: 900px) {
            .ps-grid-4,
            .ps-grid-3,
            .ps-grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>

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
        @endif

        <x-history.audit-list :audits="$audits" />

        <x-history.metric-explanation />
    </div>
</x-filament-panels::page>
