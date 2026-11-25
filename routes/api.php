<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompetitionController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('permission:competitions.view')->group(function () {
        Route::get('/competitions', [CompetitionController::class, 'index']);
        Route::get('/competitions/{competition}', [CompetitionController::class, 'show']);
    });

    Route::middleware('permission:teams.view')->group(function () {
        Route::get('/team', [TeamController::class, 'index']);
        Route::get('/team/{team}', [TeamController::class, 'show']);
    });

    Route::get('/players', [PlayerController::class, 'index'])->middleware('permission:players.view');
});
