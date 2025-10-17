<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Input\AgronomiController;
use App\Http\Controllers\Input\HPTController;
use App\Http\Controllers\Input\GudangController;
use App\Http\Controllers\Input\RencanaKerjaHarianController;
use App\Http\Controllers\Input\KendaraanController;
use App\Http\Controllers\Input\GudangBbmController;
use App\Http\Controllers\Input\PiasController;

// =====================================
// AGRONOMI ROUTES
// =====================================
Route::group(['middleware' => ['auth', 'permission:Agronomi']], function () {
    Route::get('input/agronomi', [AgronomiController::class, 'index'])->name('input.agronomi.index');
    Route::post('input/agronomi', [AgronomiController::class, 'handle'])->name('input.agronomi.handle');
    Route::get('input/agronomi/show/{nosample}/{companycode}/{tanggalpengamatan}', [AgronomiController::class, 'show'])
        ->name('input.agronomi.show');
    Route::post('input/agronomi/get-field', [AgronomiController::class, 'getFieldByMapping'])->name('input.agronomi.getFieldByMapping');
    Route::get('input/agronomi/check-data', [AgronomiController::class, 'checkData'])->name('input.agronomi.check-data');
});

Route::get('input/agronomi/excel', [AgronomiController::class, 'excel'])
    ->name('input.agronomi.exportExcel')->middleware('permission:Excel Agronomi');
Route::get('input/agronomi/create', [AgronomiController::class, 'create'])
    ->name('input.agronomi.create')->middleware('permission:Create Agronomi');
Route::delete('input/agronomi/{nosample}/{companycode}/{tanggalpengamatan}', [AgronomiController::class, 'destroy'])
    ->name('input.agronomi.destroy')->middleware('permission:Hapus Agronomi');

Route::group(['middleware' => ['auth', 'permission:Edit Agronomi']], function () {
    Route::put('input/agronomi/{nosample}/{companycode}/{tanggalpengamatan}', [AgronomiController::class, 'update'])
        ->name('input.agronomi.update');
    Route::get('input/agronomi/{nosample}/{companycode}/{tanggalpengamatan}/edit', [AgronomiController::class, 'edit'])
        ->name('input.agronomi.edit');
});

// =====================================
// HPT ROUTES
// =====================================
Route::group(['middleware' => ['auth', 'permission:HPT']], function () {
    Route::get('input/hpt', [HPTController::class, 'index'])->name('input.hpt.index');
    Route::post('input/hpt', [HPTController::class, 'handle'])->name('input.hpt.handle');
    Route::get('input/hpt/show/{nosample}/{companycode}/{tanggalpengamatan}', [HPTController::class, 'show'])
        ->name('input.hpt.show');
    Route::post('input/hpt/get-field', [HPTController::class, 'getFieldByMapping'])->name('input.hpt.getFieldByMapping');
    Route::get('input/hpt/check-data', [HPTController::class, 'checkData'])->name('input.hpt.check-data');
});

Route::get('input/hpt/excel', [HPTController::class, 'excel'])
    ->name('input.hpt.exportExcel')->middleware('permission:Excel HPT');
Route::get('input/hpt/create', [HPTController::class, 'create'])
    ->name('input.hpt.create')->middleware('permission:Create HPT');
Route::delete('input/hpt/{nosample}/{companycode}/{tanggalpengamatan}', [HPTController::class, 'destroy'])
    ->name('input.hpt.destroy')->middleware('permission:Hapus HPT');

Route::group(['middleware' => ['auth', 'permission:Edit HPT']], function () {
    Route::put('input/hpt/{nosample}/{companycode}/{tanggalpengamatan}', [HPTController::class, 'update'])
        ->name('input.hpt.update');
    Route::get('input/hpt/{nosample}/{companycode}/{tanggalpengamatan}/edit', [HPTController::class, 'edit'])
        ->name('input.hpt.edit');
});

// =====================================
// RENCANA KERJA HARIAN ROUTES - UPDATED WITH OPERATOR REPORT
// =====================================
Route::middleware('auth')->group(function () {
    // RKH Routes Group
    Route::prefix('input/kerjaharian/rencanakerjaharian')
        ->name('input.rencanakerjaharian.')
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
            Route::get('/{rkhno}/show', [RencanaKerjaHarianController::class, 'show'])->name('show');
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

            // ✅ NEW: Operator Report routes
            Route::get('/operators-for-date', [RencanaKerjaHarianController::class, 'getOperatorsForDate'])->name('getOperatorsForDate');
            Route::post('/generate-operator-report', [RencanaKerjaHarianController::class, 'generateOperatorReport'])->name('generateOperatorReport');
            Route::get('/operator-report', [RencanaKerjaHarianController::class, 'showOperatorReport'])->name('operator-report');
            Route::get('/operator-report-data', [RencanaKerjaHarianController::class, 'getOperatorReportData'])->name('operator-report-data');

            // Other utility routes
            Route::post('/update-status', [RencanaKerjaHarianController::class, 'updateStatus'])->name('updateStatus');
            Route::get('/load-absen-by-date', [RencanaKerjaHarianController::class, 'loadAbsenByDate'])->name('loadAbsenByDate');
            Route::post('/generate-dth', [RencanaKerjaHarianController::class, 'generateDTH'])->name('generateDTH');
            Route::post('/generate-rekap-lkh', [RencanaKerjaHarianController::class, 'generateRekapLKH'])->name('generateRekapLKH');
            Route::get('/dth-report', [RencanaKerjaHarianController::class, 'showDTHReport'])->name('dth-report');
            Route::get('/rekap-lkh-report', [RencanaKerjaHarianController::class, 'showRekapLKHReport'])->name('rekap-lkh-report');
            Route::post('/{rkhno}/generate-lkh', [RencanaKerjaHarianController::class, 'manualGenerateLkh'])->name('manualGenerateLkh');
            Route::get('/dth-data', [RencanaKerjaHarianController::class, 'getDTHData'])->name('dth-data');
            Route::get('/lkh-rekap-data', [RencanaKerjaHarianController::class, 'getLKHRekapData'])->name('lkh-rekap-data');
            Route::get('/{rkhno}/material-usage', [RencanaKerjaHarianController::class, 'getMaterialUsageApi'])->name('getMaterialUsage');
            Route::post('/generate-material-usage', [RencanaKerjaHarianController::class, 'generateMaterialUsage'])->name('generateMaterialUsage');
        });
});

// =====================================
// GUDANG ROUTES (EXISTING)
// =====================================
Route::middleware('auth')->group(function () {
    Route::get('input/gudang', [GudangController::class, 'home'])->name('input.gudang.index')->middleware('permission:Menu Gudang');
    Route::get('input/gudang/detail', [GudangController::class, 'detail'])->name('input.gudang.detail')->middleware('permission:Menu Gudang');
    Route::post('input/gudang/submit', [GudangController::class, 'submit'])->name('input.gudang.submit')->middleware('permission:Menu Gudang');
    Route::any('input/gudang/retur', [GudangController::class, 'retur'])->name('input.gudang.retur')->middleware('permission:Menu Gudang');
    Route::any('input/gudang/returall', [GudangController::class, 'returAll'])->name('input.gudang.returall')->middleware('permission:Menu Gudang');

    Route::get('input/pias', [PiasController::class, 'home'])->name('input.pias.index')->middleware('permission:Menu Pias');
    Route::get('input/pias/detail', [PiasController::class, 'detail'])->name('input.pias.detail')->middleware('permission:Menu Pias');
    Route::post('input/pias/submit', [PiasController::class, 'submit'])->name('input.pias.submit')->middleware('permission:Menu Pias');
});


// =====================================
// KENDARAAN BBM SYSTEM ROUTES (EXISTING)
// =====================================
Route::middleware('auth')->group(function () {
    // Kendaraan Workshop Routes
    Route::get('input/kendaraan-workshop', [KendaraanController::class, 'index'])->name('input.kendaraan-workshop.index');
    Route::post('input/kendaraan-workshop/store', [KendaraanController::class, 'store'])->name('input.kendaraan-workshop.store');
    Route::put('input/kendaraan-workshop/update', [KendaraanController::class, 'update'])->name('input.kendaraan-workshop.update');
    Route::post('input/kendaraan-workshop/{lkhno}/mark-printed', [KendaraanController::class, 'markPrinted'])->name('input.kendaraan-workshop.mark-printed');
    Route::get('input/kendaraan-workshop/{lkhno}/print', [KendaraanController::class, 'print'])->name('input.kendaraan-workshop.print');

    // Gudang BBM Routes
    Route::get('input/gudang-bbm', [GudangBbmController::class, 'index'])->name('input.gudang-bbm.index');
    Route::get('input/gudang-bbm/{ordernumber}', [GudangBbmController::class, 'show'])->name('input.gudang-bbm.show');
    Route::post('input/gudang-bbm/{ordernumber}/confirm', [GudangBbmController::class, 'markConfirmed'])->name('input.gudang-bbm.confirm');
});