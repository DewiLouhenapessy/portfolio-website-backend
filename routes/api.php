<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Beveiligde routes: alleen bereikbaar met een geldige sessie
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);

    // Voorbeeld kanban-routes
    Route::get('/boards/{board}', [\App\Http\Controllers\BoardController::class, 'show']);
    Route::post('/cards', [\App\Http\Controllers\BoardController::class, 'storeCard']);
    Route::patch('/cards/{card}', [\App\Http\Controllers\BoardController::class, 'updateCard']);
    Route::patch('/cards/{card}/move', [\App\Http\Controllers\BoardController::class, 'moveCard']);
    Route::delete('/cards/{card}', [\App\Http\Controllers\BoardController::class, 'destroyCard']);
});