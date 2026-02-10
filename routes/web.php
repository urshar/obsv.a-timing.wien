<?php

use App\Http\Controllers\ContinentController;
use App\Http\Controllers\LenexImportController;
use App\Http\Controllers\LenexMeetStructureController;
use App\Http\Controllers\MeetController;
use App\Http\Controllers\MeetStructureController;
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

Route::resource('meets', MeetController::class);

Route::prefix('meets/{meet}/structure')->name('meets.structure.')->group(function () {
    Route::get('/', [MeetStructureController::class, 'show'])->name('show');
    Route::get('/tree', [MeetStructureController::class, 'tree'])->name('tree');

    Route::get('/events/{event}/edit', [MeetStructureController::class, 'editEvent'])->name('events.edit');
    Route::put('/events/{event}', [MeetStructureController::class, 'updateEvent'])->name('events.update');
    Route::get('/sessions/{session}/events/create', [MeetStructureController::class, 'createEvent'])
        ->name('events.create');
    Route::post('/sessions/{session}/events', [MeetStructureController::class, 'storeEvent'])
        ->name('events.store');
    Route::delete('/events/{event}', [MeetStructureController::class, 'destroyEvent'])
        ->name('events.destroy');

    Route::get('/events/{event}/age-groups', [MeetStructureController::class, 'editEventAgeGroups'])
        ->name('events.age_groups.edit');
    Route::put('/events/{event}/age-groups', [MeetStructureController::class, 'updateEventAgeGroups'])
        ->name('events.age_groups.update');

    // AgrGroups CRUD
    Route::get('age-groups/create', [MeetStructureController::class, 'createAgeGroup'])
        ->name('age_groups.create');
    Route::post('age-groups', [MeetStructureController::class, 'storeAgeGroup'])
        ->name('age_groups.store');
    Route::get('age-groups/{ageGroup}/edit', [MeetStructureController::class, 'editAgeGroup'])
        ->name('age_groups.edit');
    Route::put('age-groups/{ageGroup}', [MeetStructureController::class, 'updateAgeGroup'])
        ->name('age_groups.update');
    Route::delete('age-groups/{ageGroup}', [MeetStructureController::class, 'destroyAgeGroup'])
        ->name('age_groups.destroy');

    // AgeGroups → Events Assign (Pivot)
    Route::get('age-groups/{ageGroup}/assign', [MeetStructureController::class, 'editAgeGroupEvents'])
        ->name('age_groups.assign.edit');
    Route::put('age-groups/{ageGroup}/assign', [MeetStructureController::class, 'updateAgeGroupEvents'])
        ->name('age_groups.assign.update');

    // Sessions CRUD
    Route::get('/sessions/create', [MeetStructureController::class, 'createSession'])->name('sessions.create');
    Route::post('/sessions', [MeetStructureController::class, 'storeSession'])->name('sessions.store');
    Route::get('/sessions/{session}/edit', [MeetStructureController::class, 'editSession'])->name('sessions.edit');
    Route::put('/sessions/{session}', [MeetStructureController::class, 'updateSession'])->name('sessions.update');
    Route::delete('/sessions/{session}', [MeetStructureController::class, 'destroySession'])->name('sessions.destroy');
});

Route::prefix('imports/lenex')->name('imports.lenex.')->group(function () {
    Route::get('/', [LenexImportController::class, 'create'])->name('create');
    Route::post('/', [LenexImportController::class, 'store'])->name('store');

    Route::get('/batch/{batch}', [LenexImportController::class, 'preview'])->name('preview');

    Route::post('/batch/{batch}/map', [LenexImportController::class, 'map'])->name('map');
    Route::post('/batch/{batch}/commit', [LenexImportController::class, 'commit'])->name('commit');
    Route::post('batch/{batch}/abort', [LenexImportController::class, 'abort'])->name('abort');

    Route::get('/batch/{batch}/meet-structure', [LenexMeetStructureController::class, 'show'])
        ->name('meet_structure.show');

    // NEU: Tree + Detail (Splash-like)
    Route::get('/batch/{batch}/meet-structure/tree', [LenexMeetStructureController::class, 'tree'])
        ->name('meet_structure.tree');
    Route::get('/batch/{batch}/meet-structure/events/{event}/edit', [LenexMeetStructureController::class, 'editEvent'])
        ->name('meet_structure.events.edit');
    Route::put('/batch/{batch}/meet-structure/events/{event}', [LenexMeetStructureController::class, 'updateEvent'])
        ->name('meet_structure.events.update');
    Route::get('/batch/{batch}/meet-structure/events/{event}/age-groups',
        [LenexMeetStructureController::class, 'editEventAgeGroups'])->name('meet_structure.events.age_groups.edit');
    //    Route::put('/batch/{batch}/meet-structure/events/{event}/age-groups',
    //        [LenexMeetStructureController::class, 'updateEventAgeGroups'])->name('meet_structure.events.age_groups.update');

    Route::get('/history', [LenexImportController::class, 'history'])->name('history');
    Route::get('/history/{batch}', [LenexImportController::class, 'historyShow'])->name('history.show');
});
