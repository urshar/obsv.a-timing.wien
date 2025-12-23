<?php

use App\Http\Controllers\Api\ParaSwimStyleController;
use Illuminate\Support\Facades\Route;

/**
 * ParaSwimStyles (ohne Middleware)
 *
 * CRUD: über ID (Route Model Binding default)
 * Import/Lookup: resolve (und optional by-key)
 */

// Liste + Filter (für Dropdowns, Admin, AJAX)
Route::get('/para-swim-styles', [ParaSwimStyleController::class, 'index']);

// LENEX Resolver: distance + stroke + relay_count -> Style
Route::get('/para-swim-styles/resolve', [ParaSwimStyleController::class, 'resolve']);

// Optional: direkter Lookup per key (robust auch mit ":"), z.B. /api/para-swim-styles/by-key/4x25:FR
Route::get('/para-swim-styles/by-key/{key}', [ParaSwimStyleController::class, 'byKey'])
    ->where('key', '.*');

// CRUD (über ID)
Route::post('/para-swim-styles', [ParaSwimStyleController::class, 'store']);
Route::get('/para-swim-styles/{paraSwimStyle}', [ParaSwimStyleController::class, 'show']);
Route::put('/para-swim-styles/{paraSwimStyle}', [ParaSwimStyleController::class, 'update']);
Route::delete('/para-swim-styles/{paraSwimStyle}', [ParaSwimStyleController::class, 'destroy']);
