<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// RNH_PHASE1_ROUTES
require __DIR__ . '/rnh.php';

/*
|--------------------------------------------------------------------------
| Release Notes Helper settings
|--------------------------------------------------------------------------
*/




// RNH_SETTINGS_ROUTES_BEGIN
\Illuminate\Support\Facades\Route::get('/rnh/settings', [\App\Http\Controllers\Rnh\SettingsController::class, 'index'])
    ->name('rnh.settings.index');

\Illuminate\Support\Facades\Route::post('/rnh/settings', [\App\Http\Controllers\Rnh\SettingsController::class, 'update'])
    ->name('rnh.settings.update');

\Illuminate\Support\Facades\Route::post('/rnh/settings/create-missing', [\App\Http\Controllers\Rnh\SettingsController::class, 'createMissingDirectories'])
    ->name('rnh.settings.create-missing');

\Illuminate\Support\Facades\Route::get('/rnh/settings/browse', [\App\Http\Controllers\Rnh\SettingsController::class, 'browse'])
    ->name('rnh.settings.browse');

\Illuminate\Support\Facades\Route::post('/rnh/settings/mkdir', [\App\Http\Controllers\Rnh\SettingsController::class, 'mkdir'])
    ->name('rnh.settings.mkdir');

\Illuminate\Support\Facades\Route::post('/rnh/settings/test-git', [\App\Http\Controllers\Rnh\SettingsController::class, 'testGit'])
    ->name('rnh.settings.test-git');

\Illuminate\Support\Facades\Route::post('/rnh/settings/test-gemini', [\App\Http\Controllers\Rnh\SettingsController::class, 'testGemini'])
    ->name('rnh.settings.test-gemini');
// RNH_SETTINGS_ROUTES_END

// RNH_SERVICES_ROUTES_BEGIN
\Illuminate\Support\Facades\Route::get('/rnh/services/{service}/refs', [\App\Http\Controllers\Rnh\ServicesController::class, 'refs'])
    ->name('rnh.services.refs');

\Illuminate\Support\Facades\Route::post('/rnh/services/{service}/compare', [\App\Http\Controllers\Rnh\ServicesController::class, 'compare'])
    ->name('rnh.services.compare');

\Illuminate\Support\Facades\Route::post('/rnh/services/{service}/ai-send', [\App\Http\Controllers\Rnh\ServicesController::class, 'sendAi'])
    ->name('rnh.services.ai_send');
\Illuminate\Support\Facades\Route::post('/rnh/services/sync-all', [\App\Http\Controllers\Rnh\ServicesController::class, 'syncAllRefs'])
    ->name('rnh.services.sync_all');

\Illuminate\Support\Facades\Route::get('/rnh/services', [\App\Http\Controllers\Rnh\ServicesController::class, 'index'])
    ->name('rnh.services');

\Illuminate\Support\Facades\Route::get('/rnh/services/{service}/commits', [\App\Http\Controllers\Rnh\ServicesController::class, 'commits'])
    ->name('rnh.services.commits');

\Illuminate\Support\Facades\Route::post('/rnh/services/{service}/sync', [\App\Http\Controllers\Rnh\ServicesController::class, 'syncRefs'])
    ->name('rnh.services.sync');

\Illuminate\Support\Facades\Route::post('/rnh/services', [\App\Http\Controllers\Rnh\ServicesController::class, 'store'])
    ->name('rnh.services.store');

\Illuminate\Support\Facades\Route::put('/rnh/services/{service}', [\App\Http\Controllers\Rnh\ServicesController::class, 'update'])
    ->name('rnh.services.update');

\Illuminate\Support\Facades\Route::delete('/rnh/services/{service}', [\App\Http\Controllers\Rnh\ServicesController::class, 'destroy'])
    ->name('rnh.services.destroy');
// RNH_SERVICES_ROUTES_END

Route::post('/rnh/output/save', [\App\Http\Controllers\Rnh\RnhController::class, 'saveOutputFile'])->name('rnh.output.save');

Route::get('/rnh/output/download', [\App\Http\Controllers\Rnh\RnhController::class, 'downloadOutputPath'])->name('rnh.output.download');

Route::post('/rnh/templates/save', [\App\Http\Controllers\Rnh\RnhController::class, 'saveTemplate'])->name('rnh.templates.save');
Route::post('/rnh/templates/{id}/git-diffs', [\App\Http\Controllers\Rnh\RnhController::class, 'templateGitDiffs'])->name('rnh.templates.git-diffs');
Route::post('/rnh/templates/{id}/scan', [\App\Http\Controllers\Rnh\RnhController::class, 'templateScan'])->name('rnh.templates.scan');
Route::post('/rnh/templates/{id}/release-notes/payload', [\App\Http\Controllers\Rnh\RnhController::class, 'templateReleaseNotesPayload'])->name('rnh.templates.release-notes.payload');
Route::delete('/rnh/templates/{id}', [\App\Http\Controllers\Rnh\RnhController::class, 'deleteTemplate'])->name('rnh.templates.delete');

\Illuminate\Support\Facades\Route::post('/rnh/releases/installer-preview', [\App\Http\Controllers\Rnh\RnhController::class, 'installerPreview'])->name('rnh.releases.installer-preview');

// RNH_RELEASES_INSTALLER_TEMPLATE_SAVE_V23_BEGIN
Route::post('/rnh/releases/installer-template-save', [\App\Http\Controllers\Rnh\RnhController::class, 'saveInstallerTemplate'])
    ->name('rnh.releases.installer-template-save');
// RNH_RELEASES_INSTALLER_TEMPLATE_SAVE_V23_END

