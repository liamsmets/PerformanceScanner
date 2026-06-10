<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WebsitePage extends Model
{
    protected $fillable = [
        'website_id',
        'name',
        'url',
        'is_active',
        'is_scanning',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_scanning' => 'boolean',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class);
    }

    public function lastAudit(): HasOne
    {
        return $this->hasOne(Audit::class)->latestOfMany('scanned_at');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    public function latestScanLog(): HasOne
    {
        return $this->hasOne(ScanLog::class)
            ->latestOfMany('started_at');
    }
}
