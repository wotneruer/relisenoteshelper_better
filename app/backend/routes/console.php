<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// RNH_PHASE1_IMPORT_COMMAND
Artisan::command('rnh:import-legacy {--path=/app/data} {--fresh}', function () {
    $path = (string) $this->option('path');
    $fresh = (bool) $this->option('fresh');

    $this->warn('Importing legacy Release Notes Helper data');
    $this->line('Path: ' . $path);
    $this->line('Fresh: ' . ($fresh ? 'yes' : 'no'));

    $stats = app(\App\Services\Rnh\LegacyDataImporter::class)->import($path, $fresh);

    foreach ($stats as $key => $value) {
        $this->line(str_pad($key, 28) . $value);
    }

    $this->info('Done.');
})->purpose('Import old ReleaseNotesHelper JSON data into database');
