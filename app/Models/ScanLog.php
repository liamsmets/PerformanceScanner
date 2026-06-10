<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanLog extends Model
{
    protected $fillable = [
        'website_id',
        'website_page_id',
        'audit_id',
        'target_url',
        'status',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function websitePage(): BelongsTo
    {
        return $this->belongsTo(WebsitePage::class);
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }
}
