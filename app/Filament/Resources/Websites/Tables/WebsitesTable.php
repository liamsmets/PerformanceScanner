<?php

namespace App\Filament\Resources\Websites\Tables;

use App\Filament\Resources\WebsitePages\WebsitePageResource;
use App\Filament\Resources\Websites\WebsiteResource;
use App\Jobs\RunWebsiteAuditScan;
use App\Jobs\RunWebsitePageAuditScan;
use App\Models\Website;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Services\WebsitePageDiscoveryService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;

class WebsitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(fn (Website $record): string => $record->name),

                TextColumn::make('url')
                    ->label('Website')
                    ->searchable()
                    ->sortable()
                    ->limit(35)
                    ->tooltip(fn (Website $record): string => $record->url),

                IconColumn::make('is_active')
                    ->label('Actief')
                    ->boolean(),

                TextColumn::make('pages_count')
                    ->label('Aantal pagina’s')
                    ->sortable()
                    ->url(fn (Website $record): string => WebsitePageResource::getUrl('index') . '?website_id=' . $record->id)
                    ->color('info'),

                TextColumn::make('audits_count')
                    ->label('Aantal audits')
                    ->sortable(),

                TextColumn::make('scan_state')
                    ->label('Laatste scan')
                    ->state(function (Website $record): string {
                        if ($record->is_scanning) {
                            return 'Scan bezig...';
                        }

                        return $record->lastAudit?->scanned_at?->format('d/m/Y H:i')
                            ?? 'Nog niet gescand';
                    })
                    ->badge()
                    ->color(fn (Website $record): string => $record->is_scanning ? 'warning' : 'gray'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('searchPages')
                        ->label('Pagina’s zoeken')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('success')
                        ->modalHeading('Pagina’s zoeken')
                        ->modalDescription('Selecteer welke gevonden pagina’s je wil toevoegen.')
                        ->modalSubmitActionLabel('Geselecteerde pagina’s toevoegen')
                        ->disabled(fn (Website $record): bool => ! $record->is_active)
                        ->form(function (Website $record): array {
                            $service = app(WebsitePageDiscoveryService::class);

                            $urls = $service->discoverUrls($record);

                            $mainWebsiteUrl = $service->normalizeForComparison($record->url);

                            $existingUrls = $record->pages()
                                ->pluck('url')
                                ->map(fn (string $url) => $service->normalizeForComparison($url))
                                ->toArray();

                            $urls = collect($urls)
                                ->filter(function (string $url) use ($service, $mainWebsiteUrl, $existingUrls): bool {
                                    $normalizedUrl = $service->normalizeForComparison($url);

                                    if ($normalizedUrl === $mainWebsiteUrl) {
                                        return false;
                                    }

                                    if (in_array($normalizedUrl, $existingUrls, true)) {
                                        return false;
                                    }

                                    return true;
                                })
                                ->values()
                                ->toArray();

                            if (empty($urls)) {
                                return [
                                    Placeholder::make('no_pages_found')
                                        ->label('Geen nieuwe pagina’s gevonden')
                                        ->content('De hoofdwebsite en pagina’s die al bestaan worden niet opnieuw getoond.'),
                                ];
                            }

                            $options = collect($urls)
                                ->mapWithKeys(fn (string $url) => [$url => $url])
                                ->toArray();

                            return [
                                CheckboxList::make('urls')
                                    ->label('Gevonden pagina’s')
                                    ->options($options)
                                    ->searchable()
                                    ->searchPrompt('Zoek op URL...')
                                    ->noSearchResultsMessage('Geen pagina’s gevonden voor deze zoekterm.')
                                    ->required()
                                    ->columns(1)
                                    ->bulkToggleable(),
                            ];
                        })
                        ->action(function (Website $record, array $data): void {
                            $selectedUrls = $data['urls'] ?? [];

                            if (empty($selectedUrls)) {
                                Notification::make()
                                    ->title('Geen pagina’s toegevoegd')
                                    ->body('Er werd geen nieuwe pagina geselecteerd of er waren geen nieuwe pagina’s beschikbaar.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $service = app(WebsitePageDiscoveryService::class);

                            $created = 0;
                            $existing = 0;

                            foreach ($selectedUrls as $url) {
                                $page = $record->pages()->firstOrCreate(
                                    ['url' => $url],
                                    [
                                        'name' => $service->makeNameFromUrl($url),
                                        'is_active' => true,
                                    ]
                                );

                                if ($page->wasRecentlyCreated) {
                                    $created++;
                                } else {
                                    $existing++;
                                }
                            }

                            Notification::make()
                                ->title('Pagina’s toegevoegd')
                                ->body($created . ' nieuwe pagina’s toegevoegd. ' . $existing . ' bestonden al.')
                                ->success()
                                ->send();
                        }),
                    Action::make('addPage')
                        ->label('Pagina toevoegen')
                        ->icon('heroicon-o-plus')
                        ->color('gray')
                        ->form([
                            TextInput::make('name')
                                ->label('Naam')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Contact'),

                            TextInput::make('url')
                                ->label('URL')
                                ->url()
                                ->required()
                                ->maxLength(255)
                                ->placeholder('https://quickstream.be/contact'),

                            Toggle::make('is_active')
                                ->label('Actief')
                                ->default(true),
                        ])
                        ->action(function (Website $record, array $data): void {
                            $record->pages()->create($data);

                            Notification::make()
                                ->title('Pagina toegevoegd')
                                ->body('De pagina is toegevoegd aan ' . $record->name . '.')
                                ->success()
                                ->send();
                        }),

                    Action::make('history')
                        ->label('Historiek')
                        ->icon('heroicon-o-chart-bar')
                        ->color('info')
                        ->url(fn (Website $record): string => WebsiteResource::getUrl('history', [
                            'record' => $record,
                        ])),

                    Action::make('scan')
                        ->label(fn (Website $record): string => $record->is_scanning ? 'Scan bezig...' : 'Scan hoofdwebsite')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Website scannen')
                        ->modalDescription('Deze actie zet een scan in de wachtrij. De audit verschijnt daarna bij Audits.')
                        ->disabled(fn (Website $record): bool => ! $record->is_active || $record->is_scanning)
                        ->action(function (Website $record): void {
                            $record->update([
                                'is_scanning' => true,
                            ]);

                            dispatch(new RunWebsiteAuditScan($record->id));

                            Notification::make()
                                ->title('Scan gestart')
                                ->body('De scan voor ' . $record->name . ' is in de wachtrij gezet.')
                                ->success()
                                ->send();
                        }),

                    Action::make('scanPages')
                        ->label('Scan pagina’s')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Alle actieve pagina’s scannen')
                        ->modalDescription('Deze actie zet voor elke actieve pagina van deze website een Lighthouse-scan in de wachtrij.')
                        ->disabled(fn (Website $record): bool => ! $record->is_active)
                        ->action(function (Website $record): void {
                            $pages = $record->pages()
                                ->where('is_active', true)
                                ->where('is_scanning', false)
                                ->get();

                            if ($pages->isEmpty()) {
                                Notification::make()
                                    ->title('Geen actieve pagina’s')
                                    ->body('Deze website heeft nog geen actieve pagina’s om te scannen.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            foreach ($pages as $page) {
                                $page->update([
                                    'is_scanning' => true,
                                ]);

                                dispatch(new RunWebsitePageAuditScan($page->id));
                            }

                            Notification::make()
                                ->title('Pagina-scans gestart')
                                ->body($pages->count() . ' pagina’s zijn in de wachtrij gezet.')
                                ->success()
                                ->send();
                        }),

                    EditAction::make(),
                ])
                    ->label('Acties')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button()
                    ->color('gray'),
            ]);
    }
}
