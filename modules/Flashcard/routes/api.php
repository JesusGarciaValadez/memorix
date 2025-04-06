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
Route::prefix('v1')->group(function () {
    // Flashcard Routes
    Route::prefix('flashcards')->group(function () {
        Route::get('/', [FlashcardController::class, 'index']);
        Route::post('/', [FlashcardController::class, 'store']);
        Route::get('/trash', [FlashcardController::class, 'trash']);
        Route::get('/{flashcard}', [FlashcardController::class, 'show']);
        Route::put('/{flashcard}', [FlashcardController::class, 'update']);
        Route::delete('/{flashcard}', [FlashcardController::class, 'destroy']);
        Route::post('/{flashcard}/restore', [FlashcardController::class, 'restore']);
        Route::delete('/{flashcard}/force', [FlashcardController::class, 'forceDelete']);
    });

    // Study Session Routes
    Route::prefix('study-sessions')->group(function () {
        Route::post('/start', [StudySessionController::class, 'start']);
        Route::post('/{studySession}/end', [StudySessionController::class, 'end']);
        Route::get('/practice', [StudySessionController::class, 'getFlashcardsForPractice']);
        Route::post('/practice/{flashcard}', [StudySessionController::class, 'recordPracticeResult']);
        Route::post('/reset', [StudySessionController::class, 'resetPractice']);
    });

    // Statistics Routes
    Route::prefix('statistics')->group(function () {
        Route::get('/', [StatisticController::class, 'index']);
        Route::get('/success-rate', [StatisticController::class, 'successRate']);
        Route::get('/average-duration', [StatisticController::class, 'averageDuration']);
        Route::get('/total-time', [StatisticController::class, 'totalTime']);
    });

    // Log Routes
    Route::prefix('logs')->group(function () {
        Route::get('/', [LogController::class, 'index']);
        Route::get('/latest', [LogController::class, 'latest']);
    });
});
