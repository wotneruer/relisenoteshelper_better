<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commit extends Model
{
    protected $guarded = [];

    protected $casts = [
        'committed_at' => 'datetime',
        'metadata' => 'array',
    ];
}
