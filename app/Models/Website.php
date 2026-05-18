<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    // Deze velden mogen door Laravel ingevuld worden
    protected $fillable = [
        'url',
        'performance_score',
        'security_report',
        'last_scanned_at',
    ];

    // Dit zorgt dat de data types goed staan (JSON wordt Array, Datum wordt Carbon object)
    protected $casts = [
        'security_report' => 'array',
        'last_scanned_at' => 'datetime',
    ];
}
