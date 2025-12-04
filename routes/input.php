<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Input\HPTController;
use App\Http\Controllers\Input\PiasController;
use App\Http\Controllers\Input\GudangController;
use App\Http\Controllers\Input\AgronomiController;
use App\Http\Controllers\Input\RkhPanenController;
use App\Http\Controllers\Input\GudangBbmController;
use App\Http\Controllers\Input\KendaraanController;
use App\Http\Controllers\Input\RencanaKerjaHarianController;
use App\Http\Controllers\Input\RencanaKerjaMingguanController;
use App\Http\Controllers\Input\MappingBsmController;
use App\Http\Controllers\Input\NfcController;

// =====================================
// AGRONOMI ROUTES
// =====================================
Route::group(['middleware' => ['auth', 'permission:Agronomi']], function () {
    Route::get('input/agronomi', [AgronomiController::class, 'index'])->name('input.agronomi.index');
    Route::post('input/agronomi', [AgronomiController::class, 'handle'])->name('input.agronomi.handle');
    Route::get('input/agronomi/show/{nosample}/{companycode}/{tanggalpengamatan}', [AgronomiController::class, 'show'])
        ->name('input.agronomi.show');
    Route::post('input/agronomi/get-blok', [AgronomiController::class, 'getBlokbyField'])->name('input.agronomi.getBlok');
    Route::post('input/agronomi/get-var', [AgronomiController::class, 'getVarietasandKategori'])->name('input.agronomi.getVar');
    // Route::get('input/agronomi/check-data', [AgronomiController::class, 'checkData'])->name('input.agronomi.check-data');
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
Route::group(['middleware' => ['auth', 'permission:Hpt']], function () {
    Route::get('input/hpt', [HPTController::class, 'index'])->name('input.hpt.index');
    Route::post('input/hpt', [HPTController::class, 'handle'])->name('input.hpt.handle');
    Route::get('input/hpt/show/{nosample}/{companycode}/{tanggalpengamatan}', [HPTController::class, 'show'])
        ->name('input.hpt.show');
    Route::post('input/hpt/get-blok', [HPTController::class, 'getBlokbyField'])->name('input.hpt.getBlok');
    Route::post('input/hpt/get-var', [HPTController::class, 'getVarietasandKategori'])->name('input.hpt.getVar');
    // Route::get('input/hpt/check-data', [HPTController::class, 'checkData'])->name('input.hpt.check-data');
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
// RENCANA KERJA MINGGUAN ROUTES
// =====================================
Route::group(['middleware' => ['auth', 'permission:Rencana Kerja Mingguan']], function () {
    Route::match(['GET', 'POST'], 'input/rencana-kerja-mingguan', [RencanaKerjaMingguanController::class, 'index'])->name('input.rencana-kerja-mingguan.index');
    Route::get('input/rencana-kerja-mingguan/show/{rkmno}', [RencanaKerjaMingguanController::class, 'show'])->name('input.rencana-kerja-mingguan.show');
    Route::get('input/rencana-kerja-mingguan/excel', [RencanaKerjaMingguanController::class, 'excel'])->name('input.rencana-kerja-mingguan.exportExcel');
    Route::get('input/rencana-kerja-mingguan/create', [RencanaKerjaMingguanController::class, 'create'])->name('input.rencana-kerja-mingguan.create');
    Route::post('input/rencana-kerja-mingguan/store', [RencanaKerjaMingguanController::class, 'store'])->name('input.rencana-kerja-mingguan.store');
    Route::get('/getplot/{blok}', [RencanaKerjaMingguanController::class, 'getPlot'])->name('rkm.getPlot');
    Route::post('/getdata', [RencanaKerjaMingguanController::class, 'getData'])->name('rkm.getData');
    Route::put('input/rencana-kerja-mingguan/{rkmno}', [RencanaKerjaMingguanController::class, 'update'])->name('input.rencana-kerja-mingguan.update');
    Route::get('input/rencana-kerja-mingguan/{rkmno}/edit', [RencanaKerjaMingguanController::class, 'edit'])->name('input.rencana-kerja-mingguan.edit');
    Route::delete('input/rencana-kerja-mingguan/{rkmno}', [RencanaKerjaMingguanController::class, 'destroy'])->name('input.rencana-kerja-mingguan.destroy');
});


// =====================================
// RENCANA KERJA HARIAN ROUTES
// =====================================
Route::middleware('auth')->group(function () {
    Route::prefix('input/kerjaharian/rencanakerjaharian')
        ->name('input.rencanakerjaharian.')
        ->controller(RencanaKerjaHarianController::class)
        ->group(function () {

            // LKH Submit route
            Route::post('/lkh/submit', 'submitLKH')->name('submitLKH');

            // LKH Approval Detail route
            Route::get('/lkh/{lkhno}/approval-detail', 'getLkhApprovalDetail')->name('getLkhApprovalDetail');

            // Main CRUD routes
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{rkhno}/edit', 'edit')->name('edit');
            Route::put('/{rkhno}', 'update')->name('update');
            Route::get('/{rkhno}/show', 'show')->name('show');
            Route::delete('/{rkhno}', 'destroy')->name('destroy');

            // LKH related routes
            Route::get('/{rkhno}/lkh', 'getLKHData')->name('getLKHData');
            Route::get('/lkh/{lkhno}/show', 'showLKH')->name('showLKH');
            Route::get('/lkh/{lkhno}/edit', 'editLKH')->name('editLKH');
            Route::put('/lkh/{lkhno}', 'updateLKH')->name('updateLKH');

            // RKH Approval routes
            Route::get('/pending-approvals', 'getPendingApprovals')->name('getPendingApprovals');
            Route::post('/process-approval', 'processApproval')->name('processApproval');
            Route::get('/{rkhno}/approval-detail', 'getApprovalDetail')->name('getApprovalDetail');

            // LKH Approval routes
            Route::get('/pending-lkh-approvals', 'getPendingLKHApprovals')->name('getPendingLKHApprovals');
            Route::post('/process-lkh-approval', 'processLKHApproval')->name('processLKHApproval');

            // LKH Panen
            Route::get('/lkh-panen-report/get-sj', 'getSuratJalan')->name('lkh-panen-report.get-sj');

            // Operator Report routes
            Route::get('/operators-for-date', 'getOperatorsForDate')->name('getOperatorsForDate');
            Route::post('/generate-operator-report', 'generateOperatorReport')->name('generateOperatorReport');
            Route::get('/operator-report', 'showOperatorReport')->name('operator-report');
            Route::get('/operator-report-data', 'getOperatorReportData')->name('operator-report-data');

            // Panen Info route
            Route::get('/plot-info/{plot}/{activitycode}', 'getPlotInfo')->name('getPlotInfo');

            // Other utility routes
            Route::post('/check-outstanding', 'checkOutstandingRKH')->name('checkOutstanding');
            Route::post('/update-status', 'updateStatus')->name('updateStatus');
            Route::get('/load-absen-by-date', 'loadAbsenByDate')->name('loadAbsenByDate');
            Route::post('/generate-dth', 'generateDTH')->name('generateDTH');
            Route::post('/generate-rekap-lkh', 'generateRekapLKH')->name('generateRekapLKH');
            Route::get('/dth-report', 'showDTHReport')->name('dth-report');
            Route::get('/rekap-lkh-report', 'showRekapLKHReport')->name('rekap-lkh-report');
            Route::post('/{rkhno}/generate-lkh', 'manualGenerateLkh')->name('manualGenerateLkh');
            Route::get('/dth-data', 'getDTHData')->name('dth-data');
            Route::get('/lkh-rekap-data', 'getLKHRekapData')->name('lkh-rekap-data');
            Route::get('/{rkhno}/material-usage', 'getMaterialUsageApi')->name('getMaterialUsage');
            Route::post('/generate-material-usage', 'generateMaterialUsage')->name('generateMaterialUsage');
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


// =====================================
// NFC CARD MANAGEMENT ROUTES
// =====================================
Route::middleware('auth')->group(function () {
    Route::prefix('input/nfc')
        ->name('input.nfc.')
        ->controller(NfcController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/transaction-out', 'transactionOut')->name('transaction-out');
            Route::post('/transaction-in', 'transactionIn')->name('transaction-in');
            Route::post('/pos-in', 'posIn')->name('pos-in'); // NEW
            Route::post('/external-in', 'externalIn')->name('external-in');
            Route::post('/external-out', 'externalOut')->name('external-out');
        });
});

Route::group(['middleware' => ['auth', 'permission:Mapping Bsm']], function () {
    Route::match(['GET', 'POST'], 'input/mapping-bsm', [MappingBsmController::class, 'index'])->name('input.mapping-bsm.index');
    Route::get('input/mapping-bsm/get-bsm-detail', [MappingBsmController::class, 'getBsmDetail'])->name('input.mapping-bsm.get-bsm-detail');
});