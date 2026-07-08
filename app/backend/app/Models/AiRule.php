<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'metadata' => 'array',
    ];
}
