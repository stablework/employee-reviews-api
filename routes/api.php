<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\InternalAdvisorController;

Route::middleware('api-token')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
    Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);

    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/{project}/teams', [ProjectController::class, 'assignTeam']);
    Route::delete('projects/{project}/teams/{team}', [ProjectController::class, 'removeTeam']);

    Route::apiResource('reviews', ReviewController::class);

    Route::apiResource('internal-advisors', InternalAdvisorController::class, [
        'parameters' => ['internal-advisors' => 'advisor'],
    ]);
});

Route::get('/test', function (Request $request) {
    return response()->json(['message' => 'API is working']);
});
