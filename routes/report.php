<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Input\HPTController;
use App\Http\Controllers\Report\PivotController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Input\AgronomiController;

Route::group(['middleware' => ['auth', 'permission:Report Agronomi']], function () {
    Route::match(['GET', 'POST'], '/report/agronomi', [ReportController::class, 'agronomi'])->name('report.agronomi.index');
    Route::get('report/agronomi/excel', [AgronomiController::class, 'excel'])->name('report.agronomi.exportExcel');
});
Route::group(['middleware' => ['auth', 'permission:Report HPT']], function () {
    Route::match(['GET', 'POST'], 'report/hpt', [ReportController::class, 'hpt'])->name('report.hpt.index');
    Route::get('report/hpt/excel', [HPTController::class, 'excel'])->name('report.hpt.exportExcel');
});

Route::group(['middleware' => ['auth', 'permission:Report Zpk']], function () {
    Route::match(['GET', 'POST'], 'report/report-zpk', [ReportController::class, 'zpk'])->name('report.report-zpk.index');
    Route::get('report/report-zpk/excel', [ReportController::class, 'excelZPK'])->name('report.report-zpk.exportExcel');
});

// UBAH DARI:
Route::group(['middleware' => ['auth', 'permission:Panen Tebu Report']], function () {
    Route::get('report/panen-tebu-report', [ReportController::class, 'index'])
        ->name('report.panen-tebu-report.index');
});
// MENJADI:
Route::group(['middleware' => ['auth', 'permission:Panen Tebu Report']], function () {
    Route::get('report/panen-tebu-report', [ReportController::class, 'index'])
        ->name('report.panen-tebu-report.index');
});
Route::get('report/agronomipivot', [PivotController::class, 'pivotTableAgronomi'])->name('pivotTableAgronomi')
    ->middleware('permission:Pivot Agronomi');
Route::get('report/hptpivot', [PivotController::class, 'pivotTableHPT'])->name('pivotTableHPT')
    ->middleware('permission:Pivot HPT');
