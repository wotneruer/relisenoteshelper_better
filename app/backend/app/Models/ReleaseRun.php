<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseRun extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'input' => 'array',
        'summary' => 'array',
    ];
}
