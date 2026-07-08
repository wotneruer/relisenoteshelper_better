<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseTemplate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'legacy_created_at' => 'datetime',
        'legacy_updated_at' => 'datetime',
    ];
}
