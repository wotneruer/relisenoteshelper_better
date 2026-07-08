<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    protected $guarded = [];

    protected $casts = [
        'built_at' => 'datetime',
        'scope_generated_at' => 'datetime',
        'has_changes' => 'boolean',
        'metadata' => 'array',
        'legacy_created_at' => 'datetime',
    ];
}
