<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseRunService extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_previous_ancestor_of_target' => 'boolean',
        'is_target_ancestor_of_previous' => 'boolean',
        'used_merge_base_for_diff' => 'boolean',
        'jira_keys' => 'array',
        'artifacts' => 'array',
        'metadata' => 'array',
    ];
}
