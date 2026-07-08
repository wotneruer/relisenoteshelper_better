<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    protected $casts = [
        'created_from_installer' => 'boolean',
        'needs_git_url' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'legacy_created_at' => 'datetime',
    ];
}
