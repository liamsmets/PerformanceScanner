<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Website extends Model
{
    protected $fillable = [
        'name',
        'url',
        'is_active',
        'is_scanning',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_scanning' => 'boolean',
    ];

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class);
    }

    public function lastAudit(): HasOne
    {
        return $this->hasOne(Audit::class)->ofMany('scanned_at', 'max');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(WebsitePage::class);
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    public function latestMainScanLog(): HasOne
    {
        return $this->hasOne(ScanLog::class)
            ->whereNull('website_page_id')
            ->latestOfMany('started_at');
    }
}
