<?php

namespace App\Filament\Resources\WebsitePages\Tables;

use App\Jobs\RunWebsitePageAuditScan;
use App\Models\WebsitePage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\WebsitePages\WebsitePageResource;
use Illuminate\Support\Str;

class WebsitePagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->limit(15)
                    ->tooltip(fn (WebsitePage $record): string => $record->name)
                    ->sortable(),

                TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->formatStateUsing(fn (?string $state): string => $state ? Str::limit($state, 35) : '-')
                    ->tooltip(fn (WebsitePage $record): string => $record->url)
                    ->copyable()
                    ->url(fn (WebsitePage $record): string => $record->url)
                    ->openUrlInNewTab(),

                IconColumn::make('is_active')
                    ->label('Actief')
                    ->boolean(),

                TextColumn::make('audits_count')
                    ->label('Aantal audits')
                    ->sortable(),

                TextColumn::make('scan_state')
                    ->label('Laatste scan')
                    ->state(function (WebsitePage $record): string {
                        if ($record->is_scanning) {
                            return 'Scan bezig...';
                        }

                        return $record->lastAudit?->scanned_at?->format('d/m/Y H:i')
                            ?? 'Nog niet gescand';
                    })
                    ->badge()
                    ->color(fn (WebsitePage $record): string => $record->is_scanning ? 'warning' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('website_id')
                    ->label('Website')
                    ->relationship('website', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('scan')
                    ->label(fn (WebsitePage $record): string => $record->is_scanning ? 'Scan bezig...' : 'Scan')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Pagina scannen')
                    ->modalDescription('Deze actie zet een Lighthouse-scan voor deze pagina in de wachtrij.')
                    ->disabled(fn (WebsitePage $record): bool => ! $record->is_active || $record->is_scanning)
                    ->action(function (WebsitePage $record): void {
                        $record->update([
                            'is_scanning' => true,
                        ]);

                        dispatch(new RunWebsitePageAuditScan($record->id));

                        Notification::make()
                            ->title('Scan gestart')
                            ->body('De scan voor ' . $record->name . ' is in de wachtrij gezet.')
                            ->success()
                            ->send();
                    }),

                Action::make('history')
                    ->label('Historiek')
                    ->color('info')
                    ->url(fn (WebsitePage $record): string => WebsitePageResource::getUrl('history', [
                        'record' => $record,
                    ])),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
