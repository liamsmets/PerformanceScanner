<?php

namespace App\Filament\Resources\Audits\Pages;

use App\Filament\Resources\Audits\AuditResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAudit extends ViewRecord
{
    protected static string $resource = AuditResource::class;

    protected string $view = 'filament.resources.audits.pages.view-audit';

    public function getTitle(): string
    {
        return 'Audit bekijken';
    }
}
