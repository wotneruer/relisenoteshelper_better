<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    protected $guarded = [];

    protected $casts = [
        'redacted' => 'boolean',
        'metadata' => 'array',
    ];
}
