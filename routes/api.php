<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompetitionController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
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

    Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view');

    Route::middleware('permission:users.manage')->group(function () {
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
    });

    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:roles.manage|users.manage');

    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('/permissions', [RoleController::class, 'permissions']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles/{role}', [RoleController::class, 'update']);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
    });
});
