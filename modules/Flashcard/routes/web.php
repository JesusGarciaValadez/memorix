<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/flashcards', static fn () => view('flashcard::index'));
