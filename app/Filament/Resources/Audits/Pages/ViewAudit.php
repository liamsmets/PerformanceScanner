<?php

namespace App\Filament\Resources\Audits\Pages;

use App\Filament\Resources\Audits\AuditResource;
use App\Models\Audit;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewAudit extends ViewRecord
{
    protected static string $resource = AuditResource::class;

    protected function resolveRecord(int|string $key): Model
    {
        return Audit::query()
            ->with('website')
            ->findOrFail($key);
    }
}
