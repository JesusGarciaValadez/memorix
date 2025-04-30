<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Flashcard\app\Http\Controllers\Api\V1\FlashcardController;
use Modules\Flashcard\app\Http\Controllers\Api\V1\LogController;
use Modules\Flashcard\app\Http\Controllers\Api\V1\StatisticController;
use Modules\Flashcard\app\Http\Controllers\Api\V1\StudySessionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// API v1 routes
Route::prefix('v1')
    ->group(function (): void {
        Route::apiResource('flashcards', FlashcardController::class);
        Route::apiResource('study-sessions', StudySessionController::class);
        Route::apiResource('statistics', StatisticController::class);
        Route::apiResource('logs', LogController::class);
    })
    ->middleware(['auth:sanctum']);
