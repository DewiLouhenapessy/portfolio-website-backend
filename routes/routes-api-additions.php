<?php

// ─────────────────────────────────────────────────────────
// TOEVOEGEN AAN: routes/api.php
// ─────────────────────────────────────────────────────────

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController; // je eigen kanban-controller

// Publieke auth-routes (geen inlog vereist)
Route::post('/login', [AuthController::class, 'login']);

// Beveiligde routes: alleen bereikbaar met een geldige sessie
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Je kanban-routes komen ook hierbinnen te staan, bijvoorbeeld:
    Route::get('/boards/{board}', [BoardController::class, 'show']);
    Route::post('/cards', [BoardController::class, 'storeCard']);
    Route::patch('/cards/{card}', [BoardController::class, 'updateCard']);
    Route::patch('/cards/{card}/move', [BoardController::class, 'moveCard']);
    Route::delete('/cards/{card}', [BoardController::class, 'destroyCard']);
});
