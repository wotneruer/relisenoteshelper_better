<?php

use App\Http\Controllers\Rnh\RnhController;
use Illuminate\Support\Facades\Route;

Route::prefix('rnh')->name('rnh.')->group(function () {
    Route::get('/', [RnhController::class, 'dashboard'])->name('dashboard');
    Route::get('/services', [RnhController::class, 'services'])->name('services');
    Route::get('/templates', [RnhController::class, 'templates'])->name('templates');
    Route::get('/templates/{id}', [RnhController::class, 'template'])->name('templates.show');
    Route::get('/releases', [RnhController::class, 'releases'])->name('releases');
    Route::get('/runs', [RnhController::class, 'runs'])->name('runs');
    Route::get('/output', [RnhController::class, 'output'])->name('output');
    Route::get('/settings', [RnhController::class, 'settings'])->name('settings');
});
