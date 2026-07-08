<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];
}
