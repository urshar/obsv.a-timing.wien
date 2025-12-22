<?php

use App\Http\Controllers\ContinentController;
use App\Http\Controllers\NationController;
use App\Http\Controllers\NationImportController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\RegionImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::prefix('nations/import')->name('nations.import.')->group(function () {
    Route::get('/', [NationImportController::class, 'show'])->name('show');
    Route::post('/preview', [NationImportController::class, 'preview'])->name('preview');
    Route::post('/commit', [NationImportController::class, 'commit'])->name('commit');
});

Route::prefix('regions/import')->name('regions.import.')->group(function () {
    Route::get('/', [RegionImportController::class, 'show'])->name('show');
    Route::post('/preview', [RegionImportController::class, 'preview'])->name('preview');
    Route::post('/commit', [RegionImportController::class, 'commit'])->name('commit');
});

Route::resource('continents', ContinentController::class);
Route::resource('nations', NationController::class)->except(['show']);
Route::resource('regions', RegionController::class)->except(['show']);
