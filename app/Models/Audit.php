<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    protected $fillable = [
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
        'report_json',
        'scanned_at',
    ];

    protected $casts = [
        'report_json' => 'array',
        'scanned_at' => 'datetime',
        'cls' => 'decimal:3',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
    public function websitePage()
    {
        return $this->belongsTo(WebsitePage::class);
    }
}
