<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Input\HPTController;
use App\Http\Controllers\Report\PivotController;

use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Input\AgronomiController;
use App\Http\Controllers\Report\PanenTebuController;
use App\Http\Controllers\Report\MasterLahanReportController;
use App\Http\Controllers\Report\RekapUpahMingguanController;
use App\Http\Controllers\Report\SuratJalanTimbanganReportController;
use App\Http\Controllers\Report\PanenTrackPlotReportController;


Route::group(['middleware' => ['auth', 'permission:Report Agronomi']], function () {
    Route::match(['GET', 'POST'], '/report/agronomi', [ReportController::class, 'agronomi'])->name('report.agronomi.index');
    Route::get('report/agronomi/excel', [AgronomiController::class, 'excel'])->name('report.agronomi.exportExcel');
});
Route::group(['middleware' => ['auth', 'permission:Report HPT']], function () {
    Route::match(['GET', 'POST'], 'report/hpt', [ReportController::class, 'hpt'])->name('report.hpt.index');
    Route::get('report/hpt/excel', [HPTController::class, 'excel'])->name('report.hpt.exportExcel');
});

Route::group(['middleware' => ['auth', 'permission:Trash Report']], function () {
    Route::match(['GET', 'POST'], 'report/trash-report', [ReportController::class, 'trash'])->name('report.trash-report.index');
});


Route::group(['middleware' => ['auth', 'permission:Report Zpk']], function () {
    Route::match(['GET', 'POST'], 'report/report-zpk', [ReportController::class, 'zpk'])->name('report.report-zpk.index');
    Route::get('report/report-zpk/excel', [ReportController::class, 'excelZPK'])->name('report.report-zpk.exportExcel');
});

Route::group(['middleware' => ['auth', 'permission:Panen Tebu Report']], function () {
    Route::match(['GET', 'POST'], 'report/panen-tebu-report', [PanenTebuController::class, 'index'])->name('report.panen-tebu-report.index');
    Route::post('report/panen-tebu-report/proses', [PanenTebuController::class, 'proses'])->name('report.panen-tebu-report.proses');
});

Route::get('report/agronomipivot', [PivotController::class, 'pivotTableAgronomi'])->name('pivotTableAgronomi')
    ->middleware('permission:Pivot Agronomi');
Route::get('report/hptpivot', [PivotController::class, 'pivotTableHPT'])->name('pivotTableHPT')
    ->middleware('permission:Pivot HPT');

// Report Master Lahan
Route::group(['middleware' => ['auth', 'permission:Report Manajemen Lahan']], function () {
    Route::get('report/manajemen-lahan', [App\Http\Controllers\Report\MasterLahanReportController::class, 'index'])->name('report.report-manajemen-lahan.index');
    Route::get('report/manajemen-lahan/data', [App\Http\Controllers\Report\MasterLahanReportController::class, 'getData'])->name('report.report-manajemen-lahan.data');
});

// Report Surat Jalan & Timbangan
Route::group(['middleware' => ['auth', 'permission:Report Surat Jalan Timbangan']], function () {
    Route::get('report/surat-jalan-timbangan', [App\Http\Controllers\Report\SuratJalanTimbanganReportController::class, 'index'])->name('report.report-surat-jalan-timbangan.index');
    Route::get('report/surat-jalan-timbangan/data', [App\Http\Controllers\Report\SuratJalanTimbanganReportController::class, 'getData'])->name('report.report-surat-jalan-timbangan.data');

    // Detail page routes
    Route::get('report/surat-jalan-timbangan/{suratjalanno}', [App\Http\Controllers\Report\SuratJalanTimbanganReportController::class, 'show'])->name('report.report-surat-jalan-timbangan.show');
    Route::get('report/surat-jalan-timbangan/{suratjalanno}/detail', [App\Http\Controllers\Report\SuratJalanTimbanganReportController::class, 'getDetail'])->name('report.report-surat-jalan-timbangan.detail');
});

Route::group(['middleware' => ['auth', 'permission:Rekap Upah Mingguan']], function () {
    Route::match(['GET', 'POST'], 'report/rekap-upah-mingguan', [RekapUpahMingguanController::class, 'index'])->name('report.rekap-upah-mingguan.index');
    Route::get('report/rekap-upah-mingguan/excel', [RekapUpahMingguanController::class, 'excelRUM'])->name('report.rekap-upah-mingguan.exportExcel');
    Route::get('report/rekap-upah-mingguan/show/{lkhno}', [RekapUpahMingguanController::class, 'show'])->name('report.rekap-upah-mingguan.show');
    Route::match(['GET', 'POST'], 'report/rekap-upah-mingguan/preview', [RekapUpahMingguanController::class, 'previewReport'])->name('report.rekap-upah-mingguan.preview');
    Route::get('report/rekap-upah-mingguan/export-excel', [RekapUpahMingguanController::class, 'exportExcel'])->name('report.rekap-upah-mingguan.export-excel');
});

// Report Tracking Panen per Plot
Route::group(['middleware' => ['auth', 'permission:Panen Track Plot']], function () {
    Route::get('report/panen-track-plot', [PanenTrackPlotReportController::class, 'index'])
        ->name('report.panen-track-plot.index');
    Route::get('report/panen-track-plot/batches', [PanenTrackPlotReportController::class, 'getBatches'])
        ->name('report.panen-track-plot.batches');
    Route::get('report/panen-track-plot/data', [PanenTrackPlotReportController::class, 'getData'])
        ->name('report.panen-track-plot.data');
});