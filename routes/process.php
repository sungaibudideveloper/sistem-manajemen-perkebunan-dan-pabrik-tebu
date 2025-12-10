<?php

// routes\process.php

use App\Http\Controllers\Process\PostController;
use App\Http\Controllers\Process\ClosingController;
use App\Http\Controllers\Process\UnpostController;

Route::middleware('auth')->prefix('process')->name('process.')->group(function () {

    // ============================================================================
    // POSTING
    // ============================================================================
    Route::middleware('permission:process.posting.view')->group(function () {
        Route::match(['GET', 'POST'], 'posting', [PostController::class, 'index'])->name('posting');
        Route::post('posting/submit', [PostController::class, 'posting'])->name('posting.submit');
        Route::post('post-session', [PostController::class, 'postSession'])->name('postSession');
    });

    // ============================================================================
    // UNPOSTING
    // ============================================================================
    Route::middleware('permission:process.unposting.view')->group(function () {
        Route::match(['GET', 'POST'], 'unposting', [UnpostController::class, 'index'])->name('unposting');
        Route::post('unposting/submit', [UnpostController::class, 'unposting'])->name('unposting.submit');
        Route::post('unpost-session', [UnpostController::class, 'unpostSession'])->name('unpostSession');
    });

    // ============================================================================
    // CLOSING
    // ============================================================================
    Route::middleware('permission:process.closing.view')->group(function () {
        Route::get('closing', [ClosingController::class, 'closing'])->name('closing');
    });

});