<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RnhSetting extends Model
{
    protected $table = 'rnh_settings';

    protected $fillable = [
        'key',
        'value',
        'setting_group',
        'type',
        'label',
        'description',
        'is_secret',
        'is_encrypted',
        'is_readonly',
        'sort_order',
    ];

    protected $casts = [
        'is_secret' => 'boolean',
        'is_encrypted' => 'boolean',
        'is_readonly' => 'boolean',
        'sort_order' => 'integer',
    ];
}
