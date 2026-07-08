<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseBaselineItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'included' => 'boolean',
        'metadata' => 'array',
    ];
}
