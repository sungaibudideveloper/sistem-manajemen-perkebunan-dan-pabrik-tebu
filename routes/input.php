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






Route::middleware('auth')->group(function () {
    // RKH Routes Group
    Route::prefix('input/kerjaharian/rencanakerjaharian')
        ->name('input.kerjaharian.rencanakerjaharian.')
        ->controller(RencanaKerjaHarianController::class)
        ->group(function () {
            
            // LKH Submit route (changed from lock to submit)
            Route::post('/lkh/submit', [RencanaKerjaHarianController::class, 'submitLKH'])->name('submitLKH');
            
            // LKH Approval Detail route
            Route::get('/lkh/{lkhno}/approval-detail', [RencanaKerjaHarianController::class, 'getLkhApprovalDetail'])->name('getLkhApprovalDetail');
            
            // Existing routes remain the same
            Route::get('/', [RencanaKerjaHarianController::class, 'index'])->name('index');
            Route::get('/create', [RencanaKerjaHarianController::class, 'create'])->name('create');
            Route::post('/store', [RencanaKerjaHarianController::class, 'store'])->name('store');
            Route::get('/{rkhno}/edit', [RencanaKerjaHarianController::class, 'edit'])->name('edit');
            Route::put('/{rkhno}', [RencanaKerjaHarianController::class, 'update'])->name('update');
            Route::delete('/{rkhno}', [RencanaKerjaHarianController::class, 'destroy'])->name('destroy');
            
            // LKH related routes
            Route::get('/{rkhno}/lkh', [RencanaKerjaHarianController::class, 'getLKHData'])->name('getLKHData');
            Route::get('/lkh/{lkhno}/show', [RencanaKerjaHarianController::class, 'showLKH'])->name('showLKH');
            Route::get('/lkh/{lkhno}/edit', [RencanaKerjaHarianController::class, 'editLKH'])->name('editLKH');
            Route::put('/lkh/{lkhno}', [RencanaKerjaHarianController::class, 'updateLKH'])->name('updateLKH');
            
            // RKH Approval routes
            Route::get('/pending-approvals', [RencanaKerjaHarianController::class, 'getPendingApprovals'])->name('getPendingApprovals');
            Route::post('/process-approval', [RencanaKerjaHarianController::class, 'processApproval'])->name('processApproval');
            Route::get('/{rkhno}/approval-detail', [RencanaKerjaHarianController::class, 'getApprovalDetail'])->name('getApprovalDetail');
            
            // LKH Approval routes
            Route::get('/pending-lkh-approvals', [RencanaKerjaHarianController::class, 'getPendingLKHApprovals'])->name('getPendingLKHApprovals');
            Route::post('/process-lkh-approval', [RencanaKerjaHarianController::class, 'processLKHApproval'])->name('processLKHApproval');
            
            // Other utility routes
            Route::post('/update-status', [RencanaKerjaHarianController::class, 'updateStatus'])->name('updateStatus');
            Route::get('/load-absen-by-date', [RencanaKerjaHarianController::class, 'loadAbsenByDate'])->name('loadAbsenByDate');
            Route::post('/generate-dth', [RencanaKerjaHarianController::class, 'generateDTH'])->name('generateDTH');
            Route::get('/dth-report', [RencanaKerjaHarianController::class, 'showDTHReport'])->name('dth-report');
            Route::post('/{rkhno}/generate-lkh', [RencanaKerjaHarianController::class, 'manualGenerateLkh'])->name('manualGenerateLkh');
        });
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
    Route::post('input/gudang/submit', [GudangController::class, 'submit'])->name('input.gudang.submit');
//});
