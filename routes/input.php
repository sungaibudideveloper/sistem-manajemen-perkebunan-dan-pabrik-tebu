<?php
use App\Http\Controllers\Input\AgronomiController;
use App\Http\Controllers\Input\HPTController;
use App\Http\Controllers\Input\GudangController;
use App\Http\Controllers\Input\KerjaHarian\RencanaKerjaHarianController;
use App\Http\Controllers\Input\KerjaHarian\DistribusiTenagaHarianController;
use App\Http\Controllers\Input\KerjaHarian\LaporanKerjaHarianController;





Route::group(['middleware' => ['auth', 'permission:Agronomi']], function () {

    Route::get('input/agronomi', [AgronomiController::class, 'index'])->name('input.agronomi.index');
    Route::post('input/agronomi', [AgronomiController::class, 'handle'])->name('input.agronomi.handle');
    Route::get('input/agronomi/show/{nosample}/{companycode}/{tanggaltanam}', [AgronomiController::class, 'show'])
        ->name('input.agronomi.show');
    Route::post('input/agronomi/get-field', [AgronomiController::class, 'getFieldByMapping'])->name('input.agronomi.getFieldByMapping');
    Route::get('input/agronomi/check-data', [AgronomiController::class, 'checkData'])->name('input.agronomi.check-data');
});
Route::get('input/agronomi/excel', [AgronomiController::class, 'excel'])
    ->name('input.agronomi.exportExcel')->middleware('permission:Excel Agronomi');
Route::get('input/agronomi/create', [AgronomiController::class, 'create'])
    ->name('input.agronomi.create')->middleware('permission:Create Agronomi');
Route::delete('input/agronomi/{nosample}/{companycode}/{tanggaltanam}', [AgronomiController::class, 'destroy'])
    ->name('input.agronomi.destroy')->middleware('permission:Hapus Agronomi');
Route::group(['middleware' => ['auth', 'permission:Edit Agronomi']], function () {
    Route::put('input/agronomi/{nosample}/{companycode}/{tanggaltanam}', [AgronomiController::class, 'update'])
        ->name('input.agronomi.update');
    Route::get('input/agronomi/{nosample}/{companycode}/{tanggaltanam}/edit', [AgronomiController::class, 'edit'])
        ->name('input.agronomi.edit');
});

Route::group(['middleware' => ['auth', 'permission:HPT']], function () {

    Route::get('input/hpt', [HPTController::class, 'index'])->name('input.hpt.index');
    Route::post('input/hpt', [HPTController::class, 'handle'])->name('input.hpt.handle');
    Route::get('input/hpt/show/{nosample}/{companycode}/{tanggaltanam}', [HPTController::class, 'show'])
        ->name('input.hpt.show');
    Route::post('input/hpt/get-field', [HPTController::class, 'getFieldByMapping'])->name('input.hpt.getFieldByMapping');
    Route::get('input/hpt/check-data', [HPTController::class, 'checkData'])->name('input.hpt.check-data');
});
Route::get('input/hpt/excel', [HPTController::class, 'excel'])
    ->name('input.hpt.exportExcel')->middleware('permission:Excel HPT');
Route::get('input/hpt/create', [HPTController::class, 'create'])
    ->name('input.hpt.create')->middleware('permission:Create HPT');
Route::delete('input/hpt/{nosample}/{companycode}/{tanggaltanam}', [HPTController::class, 'destroy'])
    ->name('input.hpt.destroy')->middleware('permission:Hapus HPT');
Route::group(['middleware' => ['auth', 'permission:Edit HPT']], function () {

    Route::put('input/hpt/{nosample}/{companycode}/{tanggaltanam}', [HPTController::class, 'update'])
        ->name('input.hpt.update');
    Route::get('input/hpt/{nosample}/{companycode}/{tanggaltanam}/edit', [HPTController::class, 'edit'])
        ->name('input.hpt.edit');
});


//Rencana Kerja Harian
Route::group(['middleware' => ['auth', 'permission:Herbisida']], function () {
    Route::get('input/kerjaharian/rencanakerjaharian', [RencanaKerjaHarianController::class, 'index'])->name('input.kerjaharian.rencanakerjaharian.index');
    Route::post('input/kerjaharian/rencanakerjaharian', [RencanaKerjaHarianController::class, 'store'])->name('input.kerjaharian.rencanakerjaharian.store');
    Route::get('input/kerjaharian/rencanakerjaharian/create', [RencanaKerjaHarianController::class, 'create'])->name('input.kerjaharian.rencanakerjaharian.create');
    Route::get('input/kerjaharian/rencanakerjaharian/{rkhno}/edit', [RencanaKerjaHarianController::class, 'edit'])->name('input.kerjaharian.rencanakerjaharian.edit');
    Route::put('input/kerjaharian/rencanakerjaharian/{rkhno}', [RencanaKerjaHarianController::class, 'update'])->name('input.kerjaharian.rencanakerjaharian.update');
    Route::delete('input/kerjaharian/rencanakerjaharian/{rkhno}', [RencanaKerjaHarianController::class, 'destroy'])->name('input.kerjaharian.rencanakerjaharian.destroy');
    Route::post('input/kerjaharian/rencanakerjaharian/update-status', [RencanaKerjaHarianController::class, 'updateStatus'])->name('input.kerjaharian.rencanakerjaharian.updateStatus');
    Route::get('input/kerjaharian/rencanakerjaharian/{rkhno}/lkh', [RencanaKerjaHarianController::class, 'getLKHData'])->name('input.kerjaharian.rencanakerjaharian.getLKHData');
    Route::post('input/kerjaharian/rencanakerjaharian/generate-dth', [RencanaKerjaHarianController::class, 'generateDTH'])->name('input.kerjaharian.rencanakerjaharian.generateDTH');
    Route::get('/input/kerjaharian/rencanakerjaharian/absen-by-date', [RencanaKerjaHarianController::class, 'loadAbsenByDate'])->name('input.kerjaharian.rencanakerjaharian.loadAbsenByDate');
    
    Route::get('input/kerjaharian/rencanakerjaharian/dth-report', [RencanaKerjaHarianController::class, 'showDTHReport'])->name('input.kerjaharian.rencanakerjaharian.dth-report');
    Route::get('input/kerjaharian/rencanakerjaharian/dth-data', [RencanaKerjaHarianController::class, 'getDTHData'])->name('input.kerjaharian.rencanakerjaharian.dth-data');
});

Route::group(['middleware' => ['auth', 'permission:Herbisida']], function () {
    Route::get('input/kerjaharian/distribusitenagaharian', [DistribusiTenagaHarianController::class, 'index'])->name('input.kerjaharian.distribusitenagaharian.index');
});

Route::group(['middleware' => ['auth', 'permission:Herbisida']], function () {
    Route::get('input/kerjaharian/laporankerjaharian', [LaporanKerjaHarianController::class, 'index'])->name('input.kerjaharian.laporankerjaharian.index');
    Route::get('input/kerjaharian/laporankerjaharian/{lkhno}', [LaporanKerjaHarianController::class, 'show'])->name('input.kerjaharian.laporankerjaharian.show');
});




//Gudang
//Route::group(['middleware' => ['auth', 'permission:Gudang']], function () {
    Route::get('input/gudang', [GudangController::class, 'index'])->name('input.gudang.index');
    Route::get('input/gudang/home', [GudangController::class, 'home'])->name('input.gudang.home');
    Route::get('input/gudang/detail', [GudangController::class, 'detail'])->name('input.gudang.detail');
//});
