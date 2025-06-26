<?php
use App\Http\Controllers\Process\PostController;
use App\Http\Controllers\Process\ClosingController;
use App\Http\Controllers\Process\UnpostController;

Route::group(['middleware' => ['auth', 'permission:Posting']], function () {
    Route::match(['POST', 'GET'], 'process/posting', [PostController::class, 'index'])->name('process.posting');
    Route::post('process/posting/submit', [PostController::class, 'posting'])->name('process.posting.submit');
    Route::post('process/post-session', [PostController::class, 'postSession'])->name('postSession');
});

Route::group(['middleware' => ['auth', 'permission:Unposting']], function () {
    // FIX: Ubah dari '/unposting' menjadi 'process/unposting'
    Route::match(['POST', 'GET'], 'process/unposting', [UnpostController::class, 'index'])->name('process.unposting');
    Route::post('process/unposting/submit', [UnpostController::class, 'unposting'])->name('process.unposting.submit');
    Route::post('process/unpost-session', [UnpostController::class, 'unpostSession'])->name('unpostSession');
});

Route::get('process/closing', [ClosingController::class, 'closing'])->name('closing')
    ->middleware('permission:Closing');
