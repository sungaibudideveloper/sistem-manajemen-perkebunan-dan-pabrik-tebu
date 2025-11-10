<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pabrik\TrashController;

// =============================================================================
// PABRIK TRASH ROUTES
// =============================================================================


Route::group(['middleware' => ['auth', 'permission:Trash']], function () {
    Route::get('/pabrik/trash', [TrashController::class, 'index'])->name('pabrik.trash.index');
    Route::post('/pabrik/trash', [TrashController::class, 'store'])->name('pabrik.trash.store');
    
    // Tambah where constraint buat handle karakter special
    Route::post('/pabrik/trash/update/{suratjalanno}/{companycode}/{jenis}', [TrashController::class, 'update'])
         ->where('suratjalanno', '.*')  // Accept any character including dash
         ->where('companycode', '.*')
         ->where('jenis', '.*')
         ->name('pabrik.trash.update');
         
    Route::post('/pabrik/trash/delete/{suratjalanno}/{companycode}/{jenis}', [TrashController::class, 'destroy'])
         ->where('suratjalanno', '.*')
         ->where('companycode', '.*')
         ->where('jenis', '.*')
         ->name('pabrik.trash.destroy');
    
    Route::get('/pabrik/trash/surat-jalan/check', [TrashController::class, 'checkSuratJalan'])
         ->name('pabrik.trash.surat-jalan.check');
});