<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseTemplateService extends Model
{
    protected $guarded = [];

    protected $casts = [
        'included' => 'boolean',
        'ask_if_changed' => 'boolean',
        'settings' => 'array',
    ];
}
