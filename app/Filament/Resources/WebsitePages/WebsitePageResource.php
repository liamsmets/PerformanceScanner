<?php

namespace App\Filament\Resources\WebsitePages;

use App\Filament\Resources\WebsitePages\Pages\CreateWebsitePage;
use App\Filament\Resources\WebsitePages\Pages\EditWebsitePage;
use App\Filament\Resources\WebsitePages\Pages\ListWebsitePages;
use App\Filament\Resources\WebsitePages\Pages\WebsitePageHistory;
use App\Filament\Resources\WebsitePages\Schemas\WebsitePageForm;
use App\Filament\Resources\WebsitePages\Tables\WebsitePagesTable;
use App\Models\WebsitePage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsitePageResource extends Resource
{
    protected static ?string $model = WebsitePage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?string $navigationLabel = 'Pagina’s';

    protected static ?string $modelLabel = 'pagina';

    protected static ?string $pluralModelLabel = 'pagina’s';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return WebsitePageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebsitePagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebsitePages::route('/'),
            'create' => CreateWebsitePage::route('/create'),
            'edit' => EditWebsitePage::route('/{record}/edit'),
            'history' => WebsitePageHistory::route('/{record}/history'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('website:id,name,url')
            ->with('lastAudit')
            ->withCount('audits');
    }
}
