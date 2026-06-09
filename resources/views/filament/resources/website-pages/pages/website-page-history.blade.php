<x-filament-panels::page>
    <div class="space-y-4">
        <h2 class="text-xl font-bold">
            Historiek van {{ $this->getRecord()->name }}
        </h2>

        <div>
            Website: {{ $this->getRecord()->website->name }}
        </div>

        <div>
            URL: {{ $this->getRecord()->url }}
        </div>

        <div>
            Aantal audits: {{ $this->getRecord()->audits()->count() }}
        </div>
    </div>
</x-filament-panels::page>
