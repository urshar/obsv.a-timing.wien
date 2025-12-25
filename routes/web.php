<?php

use App\Http\Controllers\ContinentController;
use App\Http\Controllers\LenexImportController;
use App\Http\Controllers\NationController;
use App\Http\Controllers\NationImportController;
use App\Http\Controllers\ParaSwimStyleAdminController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\RegionImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

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

Route::resource('para-swim-styles', ParaSwimStyleAdminController::class)->except(['show']);

Route::prefix('imports/lenex')->name('imports.lenex.')->group(function () {
    Route::get('/', [LenexImportController::class, 'create'])->name('create');
    Route::post('/', [LenexImportController::class, 'store'])->name('store');

    Route::get('/batch/{batch}', [LenexImportController::class, 'preview'])->name('preview');

    Route::post('/batch/{batch}/map', [LenexImportController::class, 'map'])->name('map');
    Route::post('/batch/{batch}/commit', [LenexImportController::class, 'commit'])->name('commit');
});
