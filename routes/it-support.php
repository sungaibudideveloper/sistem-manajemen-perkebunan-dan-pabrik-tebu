<?php

use App\Http\Controllers\ItSupport\DeleteRkhController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('it-support')->name('it-support.')->group(function () {

    // ============================================================================
    // DELETE RKH
    // ============================================================================
    Route::middleware('permission:it-support.delete-rkh.view')->group(function () {
        Route::get('delete-rkh', [DeleteRkhController::class, 'index'])->name('delete-rkh.index');
        Route::post('delete-rkh/search', [DeleteRkhController::class, 'search'])->name('delete-rkh.search');
    });

    Route::middleware('permission:it-support.delete-rkh.delete')->group(function () {
        Route::delete('delete-rkh/{rkhno}', [DeleteRkhController::class, 'destroy'])->name('delete-rkh.destroy');
    });

});