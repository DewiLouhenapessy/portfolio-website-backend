<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use Illuminate\Support\Facades\Route;

// Beveiligde routes: alleen bereikbaar met een geldige sessie
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);

    Route::get('/boards/{board}', [BoardController::class, 'show']);
    Route::post('/boards/{board}/columns', [BoardController::class, 'storeColumn']);
    Route::delete('/boards/{board}/columns/{column}', [BoardController::class, 'destroyColumn']);
    Route::post('/cards', [BoardController::class, 'storeCard']);
    Route::patch('/cards/{card}', [BoardController::class, 'updateCard']);
    Route::patch('/cards/{card}/move', [BoardController::class, 'moveCard']);
    Route::delete('/cards/{card}', [BoardController::class, 'destroyCard']);
});
