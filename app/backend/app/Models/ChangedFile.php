<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangedFile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];
}
