<?php

// routes\input.php

use App\Http\Controllers\Input\HPTController;
use App\Http\Controllers\Input\PiasController;
use App\Http\Controllers\Input\GudangController;
use App\Http\Controllers\Input\AgronomiController;
use App\Http\Controllers\Input\GudangBbmController;
use App\Http\Controllers\Input\KendaraanController;
use App\Http\Controllers\Input\RencanaKerjaHarianController;
use App\Http\Controllers\Input\RencanaKerjaMingguanController;
use App\Http\Controllers\Input\MappingBsmController;
use App\Http\Controllers\Input\NfcController;

Route::middleware('auth')->prefix('input')->name('input.')->group(function () {

    // ============================================================================
    // AGRONOMI
    // ============================================================================
    Route::middleware('permission:input.agronomi.view')->group(function () {
        Route::get('agronomi', [AgronomiController::class, 'index'])->name('agronomi.index');
        Route::post('agronomi', [AgronomiController::class, 'handle'])->name('agronomi.handle');
        Route::get('agronomi/show/{nosample}/{companycode}/{tanggalpengamatan}', [AgronomiController::class, 'show'])->name('agronomi.show');
        Route::post('agronomi/get-blok', [AgronomiController::class, 'getBlokbyField'])->name('agronomi.getBlok');
        Route::post('agronomi/get-var', [AgronomiController::class, 'getVarietasandKategori'])->name('agronomi.getVar');
    });

    Route::middleware('permission:input.agronomi.create')->group(function () {
        Route::get('agronomi/create', [AgronomiController::class, 'create'])->name('agronomi.create');
    });

    Route::middleware('permission:input.agronomi.edit')->group(function () {
        Route::get('agronomi/{nosample}/{companycode}/{tanggalpengamatan}/edit', [AgronomiController::class, 'edit'])->name('agronomi.edit');
        Route::put('agronomi/{nosample}/{companycode}/{tanggalpengamatan}', [AgronomiController::class, 'update'])->name('agronomi.update');
    });

    Route::middleware('permission:input.agronomi.delete')->group(function () {
        Route::delete('agronomi/{nosample}/{companycode}/{tanggalpengamatan}', [AgronomiController::class, 'destroy'])->name('agronomi.destroy');
    });

    Route::middleware('permission:input.agronomi.export')->group(function () {
        Route::get('agronomi/excel', [AgronomiController::class, 'excel'])->name('agronomi.exportExcel');
    });

    // ============================================================================
    // HPT
    // ============================================================================
    Route::middleware('permission:input.hpt.view')->group(function () {
        Route::get('hpt', [HPTController::class, 'index'])->name('hpt.index');
        Route::post('hpt', [HPTController::class, 'handle'])->name('hpt.handle');
        Route::get('hpt/show/{nosample}/{companycode}/{tanggalpengamatan}', [HPTController::class, 'show'])->name('hpt.show');
        Route::post('hpt/get-blok', [HPTController::class, 'getBlokbyField'])->name('hpt.getBlok');
        Route::post('hpt/get-var', [HPTController::class, 'getVarietasandKategori'])->name('hpt.getVar');
    });

    Route::middleware('permission:input.hpt.create')->group(function () {
        Route::get('hpt/create', [HPTController::class, 'create'])->name('hpt.create');
    });

    Route::middleware('permission:input.hpt.edit')->group(function () {
        Route::get('hpt/{nosample}/{companycode}/{tanggalpengamatan}/edit', [HPTController::class, 'edit'])->name('hpt.edit');
        Route::put('hpt/{nosample}/{companycode}/{tanggalpengamatan}', [HPTController::class, 'update'])->name('hpt.update');
    });

    Route::middleware('permission:input.hpt.delete')->group(function () {
        Route::delete('hpt/{nosample}/{companycode}/{tanggalpengamatan}', [HPTController::class, 'destroy'])->name('hpt.destroy');
    });

    Route::middleware('permission:input.hpt.export')->group(function () {
        Route::get('hpt/excel', [HPTController::class, 'excel'])->name('hpt.exportExcel');
    });

    // ============================================================================
    // RENCANA KERJA MINGGUAN
    // ============================================================================
    Route::middleware('permission:input.rencanakerjamingguan.view')->group(function () {
        Route::match(['GET', 'POST'], 'rencana-kerja-mingguan', [RencanaKerjaMingguanController::class, 'index'])->name('rencana-kerja-mingguan.index');
        Route::get('rencana-kerja-mingguan/show/{rkmno}', [RencanaKerjaMingguanController::class, 'show'])->name('rencana-kerja-mingguan.show');
        Route::get('rencana-kerja-mingguan/create', [RencanaKerjaMingguanController::class, 'create'])->name('rencana-kerja-mingguan.create');
        Route::post('rencana-kerja-mingguan/store', [RencanaKerjaMingguanController::class, 'store'])->name('rencana-kerja-mingguan.store');
        Route::get('rencana-kerja-mingguan/{rkmno}/edit', [RencanaKerjaMingguanController::class, 'edit'])->name('rencana-kerja-mingguan.edit');
        Route::put('rencana-kerja-mingguan/{rkmno}', [RencanaKerjaMingguanController::class, 'update'])->name('rencana-kerja-mingguan.update');
        Route::delete('rencana-kerja-mingguan/{rkmno}', [RencanaKerjaMingguanController::class, 'destroy'])->name('rencana-kerja-mingguan.destroy');
        Route::get('rencana-kerja-mingguan/excel', [RencanaKerjaMingguanController::class, 'excel'])->name('rencana-kerja-mingguan.exportExcel');
        Route::get('/getplot/{blok}', [RencanaKerjaMingguanController::class, 'getPlot'])->name('rkm.getPlot');
        Route::post('/getdata', [RencanaKerjaMingguanController::class, 'getData'])->name('rkm.getData');
    });

    // ============================================================================
    // RENCANA KERJA HARIAN
    // ============================================================================
    Route::middleware('permission:input.rencanakerjaharian.view')->group(function () {
        Route::prefix('kerjaharian/rencanakerjaharian')->name('rencanakerjaharian.')->controller(RencanaKerjaHarianController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{rkhno}/edit', 'edit')->name('edit');
            Route::put('/{rkhno}', 'update')->name('update');
            Route::get('/{rkhno}/show', 'show')->name('show');
            Route::delete('/{rkhno}', 'destroy')->name('destroy');

            Route::post('/lkh/submit', 'submitLKH')->name('submitLKH');
            Route::get('/lkh/{lkhno}/approval-detail', 'getLkhApprovalDetail')->name('getLkhApprovalDetail');
            Route::get('/{rkhno}/lkh', 'getLKHData')->name('getLKHData');
            Route::get('/lkh/{lkhno}/show', 'showLKH')->name('showLKH');
            Route::get('/lkh/{lkhno}/edit', 'editLKH')->name('editLKH');
            Route::put('/lkh/{lkhno}', 'updateLKH')->name('updateLKH');

            Route::get('/pending-approvals', 'getPendingApprovals')->name('getPendingApprovals');
            Route::post('/process-approval', 'processApproval')->name('processApproval');
            Route::get('/{rkhno}/approval-detail', 'getApprovalDetail')->name('getApprovalDetail');

            Route::get('/pending-lkh-approvals', 'getPendingLKHApprovals')->name('getPendingLKHApprovals');
            Route::post('/process-lkh-approval', 'processLKHApproval')->name('processLKHApproval');

            Route::get('/lkh-panen-report/get-sj', 'getSuratJalan')->name('lkh-panen-report.get-sj');

            Route::get('/operators-for-date', 'getOperatorsForDate')->name('getOperatorsForDate');
            Route::post('/generate-operator-report', 'generateOperatorReport')->name('generateOperatorReport');
            Route::get('/operator-report', 'showOperatorReport')->name('operator-report');
            Route::get('/operator-report-data', 'getOperatorReportData')->name('operator-report-data');

            Route::get('/plot-info/{plot}/{activitycode}', 'getPlotInfo')->name('getPlotInfo');

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

    // ============================================================================
    // GUDANG
    // ============================================================================
    Route::middleware('permission:input.gudang.view')->group(function () {
        Route::get('gudang', [GudangController::class, 'home'])->name('gudang.index');
        Route::get('gudang/detail', [GudangController::class, 'detail'])->name('gudang.detail');
        Route::post('gudang/submit', [GudangController::class, 'submit'])->name('gudang.submit');
        Route::any('gudang/retur', [GudangController::class, 'retur'])->name('gudang.retur');
        Route::any('gudang/returall', [GudangController::class, 'returAll'])->name('gudang.returall');
    });

    // ============================================================================
    // PIAS
    // ============================================================================
    Route::middleware('permission:input.pias.view')->group(function () {
        Route::get('pias', [PiasController::class, 'home'])->name('pias.index');
        Route::get('pias/detail', [PiasController::class, 'detail'])->name('pias.detail');
        Route::post('pias/submit', [PiasController::class, 'submit'])->name('pias.submit');
    });

    // ============================================================================
    // KENDARAAN WORKSHOP
    // ============================================================================
    Route::middleware('permission:input.kendaraanworkshop.view')->group(function () {
        Route::get('kendaraan-workshop', [KendaraanController::class, 'index'])->name('kendaraan-workshop.index');
        Route::post('kendaraan-workshop/store', [KendaraanController::class, 'store'])->name('kendaraan-workshop.store');
        Route::put('kendaraan-workshop/update', [KendaraanController::class, 'update'])->name('kendaraan-workshop.update');
        Route::post('kendaraan-workshop/{lkhno}/mark-printed', [KendaraanController::class, 'markPrinted'])->name('kendaraan-workshop.mark-printed');
        Route::get('kendaraan-workshop/{lkhno}/print', [KendaraanController::class, 'print'])->name('kendaraan-workshop.print');
    });

    // ============================================================================
    // GUDANG BBM
    // ============================================================================
    Route::middleware('permission:input.gudangbbm.view')->group(function () {
        Route::get('gudang-bbm', [GudangBbmController::class, 'index'])->name('gudang-bbm.index');
        Route::get('gudang-bbm/{ordernumber}', [GudangBbmController::class, 'show'])->name('gudang-bbm.show');
        Route::post('gudang-bbm/{ordernumber}/confirm', [GudangBbmController::class, 'markConfirmed'])->name('gudang-bbm.confirm');
    });

    // ============================================================================
    // NFC CARD MANAGEMENT
    // ============================================================================
    Route::middleware('permission:input.nfc.view')->group(function () {
        Route::prefix('nfc')->name('nfc.')->controller(NfcController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/transaction-out', 'transactionOut')->name('transaction-out');
            Route::post('/transaction-in', 'transactionIn')->name('transaction-in');
            Route::post('/pos-in', 'posIn')->name('pos-in');
            Route::post('/external-in', 'externalIn')->name('external-in');
            Route::post('/external-out', 'externalOut')->name('external-out');
        });
    });

    // ============================================================================
    // MAPPING BSM
    // ============================================================================
    Route::middleware('permission:input.mappingbsm.view')->group(function () {
        Route::match(['GET', 'POST'], 'mapping-bsm', [MappingBsmController::class, 'index'])->name('mapping-bsm.index');
        Route::get('mapping-bsm/get-bsm-detail', [MappingBsmController::class, 'getBsmDetail'])->name('mapping-bsm.get-bsm-detail');
        Route::post('input/update-bsm', [MappingBsmController::class, 'updateBsm'])->name('input.mapping-bsm.update-bsm');
        Route::post('input/update-bsm-bulk', [MappingBsmController::class, 'updateBsmBulk'])->name('input.mapping-bsm.update-bsm-bulk');
        Route::get('input/get-bsm-for-copy', [MappingBsmController::class, 'getBsmForCopy'])->name('input.mapping-bsm.get-bsm-for-copy');
        Route::post('input/copy-bsm', [MappingBsmController::class, 'copyBsm'])->name('input.mapping-bsm.copy-bsm');
    });

    
});