<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Input\HPTController;
use App\Http\Controllers\Input\AgronomiController;

use App\Http\Controllers\Report\PivotController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Report\PanenTebuController;
use App\Http\Controllers\Report\MasterLahanReportController;



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
