<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pabrik\TrashController;

// =============================================================================
// PABRIK TRASH ROUTES
// =============================================================================


Route::group(['middleware' => ['auth', 'permission:Trash']], function () {
    Route::get('/pabrik/trash', [TrashController::class, 'index'])->name('pabrik.trash.index');
    Route::post('/pabrik/trash', [TrashController::class, 'store'])->name('pabrik.trash.store');
    
    // Route untuk check surat jalan
    Route::get('/pabrik/trash/surat-jalan/check', [TrashController::class, 'checkSuratJalan'])
         ->name('pabrik.trash.surat-jalan.check');
});

Route::put('/pabrik/trash/{id}', [TrashController::class, 'update'])
    ->name('pabrik.trash.update')
    ->middleware(['auth', 'permission:Edit Trash']);

Route::delete('/pabrik/trash/{id}', [TrashController::class, 'destroy'])
    ->name('pabrik.trash.destroy')
    ->middleware(['auth', 'permission:Hapus Trash']);