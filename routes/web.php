<?php

use App\Http\Controllers\ContinentController;
use App\Http\Controllers\NationController;
use App\Http\Controllers\NationImportController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\RegionImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('continents', ContinentController::class);

Route::resource('nations', NationController::class);
Route::get('nations/import', [NationImportController::class, 'show'])->name('nations.import.show');
Route::post('nations/import/preview', [NationImportController::class, 'preview'])->name('nations.import.preview');
Route::post('nations/import/commit', [NationImportController::class, 'commit'])->name('nations.import.commit');

Route::resource('regions', RegionController::class);
Route::get('regions/import', [RegionImportController::class, 'show'])->name('regions.import.show');
Route::post('regions/import/preview', [RegionImportController::class, 'preview'])->name('regions.import.preview');
Route::post('regions/import/commit', [RegionImportController::class, 'commit'])->name('regions.import.commit');
