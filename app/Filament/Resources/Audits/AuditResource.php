<?php

namespace App\Filament\Resources\Audits;

use App\Filament\Resources\Audits\Pages\ListAudits;
use App\Filament\Resources\Audits\Pages\ViewAudit;
use App\Filament\Resources\Audits\Tables\AuditsTable;
use App\Models\Audit;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $navigationLabel = 'Audits';

    protected static ?string $modelLabel = 'audit';

    protected static ?string $pluralModelLabel = 'audits';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return AuditsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make('Scanoverzicht')
                            ->schema([
                                TextEntry::make('website.name')
                                    ->label('Website'),

                                TextEntry::make('website_page_id')
                                    ->label('Type')
                                    ->badge()
                                    ->formatStateUsing(fn (?int $state): string => $state === null ? 'Hoofdwebsite' : 'Pagina')
                                    ->color(fn (?int $state): string => $state === null ? 'gray' : 'info'),

                                TextEntry::make('websitePage.name')
                                    ->label('Pagina')
                                    ->placeholder('Hoofdwebsite'),

                                TextEntry::make('target_url')
                                    ->label('URL')
                                    ->state(fn (Audit $record): ?string => $record->websitePage?->url ?? $record->website?->url)
                                    ->copyable()
                                    ->url(fn (Audit $record): ?string => $record->websitePage?->url ?? $record->website?->url)
                                    ->openUrlInNewTab(),

                                TextEntry::make('scanned_at')
                                    ->label('Gescand op')
                                    ->dateTime('d/m/Y H:i:s'),

                                TextEntry::make('runs_used')
                                    ->label('Aantal gebruikte runs')
                                    ->placeholder('Niet beschikbaar'),
                            ]),

                        Section::make('Gemiddelde scores')
                            ->schema([
                                TextEntry::make('performance_score')
                                    ->label('Performance')
                                    ->badge()
                                    ->color(fn ($state): string => self::getScoreColor($state)),

                                TextEntry::make('accessibility_score')
                                    ->label('Accessibility')
                                    ->badge()
                                    ->color(fn ($state): string => self::getScoreColor($state)),

                                TextEntry::make('best_practices_score')
                                    ->label('Best Practices')
                                    ->badge()
                                    ->color(fn ($state): string => self::getScoreColor($state)),

                                TextEntry::make('seo_score')
                                    ->label('SEO')
                                    ->badge()
                                    ->color(fn ($state): string => self::getScoreColor($state)),
                            ]),
                    ]),

                Section::make('Extra metrics')
                    ->schema([
                        TextEntry::make('lcp_ms')
                            ->label('Largest Contentful Paint (LCP)')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 0, ',', '.') . ' ms' : 'Niet beschikbaar'),

                        TextEntry::make('fcp_ms')
                            ->label('First Contentful Paint (FCP)')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 0, ',', '.') . ' ms' : 'Niet beschikbaar'),

                        TextEntry::make('tbt_ms')
                            ->label('Total Blocking Time (TBT)')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 0, ',', '.') . ' ms' : 'Niet beschikbaar'),

                        TextEntry::make('cls')
                            ->label('Cumulative Layout Shift (CLS)')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 3, ',', '.') : 'Niet beschikbaar'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAudits::route('/'),
            'view' => ViewAudit::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select([
                'id',
                'website_id',
                'website_page_id',
                'runs_used',
                'performance_score',
                'accessibility_score',
                'best_practices_score',
                'seo_score',
                'lcp_ms',
                'fcp_ms',
                'tbt_ms',
                'cls',
                'scanned_at',
                'created_at',
                'updated_at',
            ])
            ->with([
                'website:id,name,url',
                'websitePage:id,website_id,name,url',
            ]);
    }

    private static function getScoreColor(?int $score): string
    {
        if ($score === null) {
            return 'gray';
        }

        if ($score <= 25) {
            return 'danger';
        }

        if ($score <= 50) {
            return 'warning';
        }

        if ($score <= 75) {
            return 'info';
        }

        return 'success';
    }
}
