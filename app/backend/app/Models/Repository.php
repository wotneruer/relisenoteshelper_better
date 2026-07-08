<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_fetch_at' => 'datetime',
        'last_scan_at' => 'datetime',
        'metadata' => 'array',
    ];
}
