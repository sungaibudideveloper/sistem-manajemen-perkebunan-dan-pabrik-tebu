<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GPXController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PivotController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UnpostController;
use App\Http\Controllers\ClosingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\UsernameLoginController;

Route::get('/login', [UsernameLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UsernameLoginController::class, 'login'])->name('login');
Route::any('/logout', [UsernameLoginController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/',  [HomeController::class, 'index'])
        ->name('home');
    Route::post('/set-session', [HomeController::class, 'setSession'])->name('setSession');

    Route::get('/notification',  [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::group(['middleware' => ['auth', 'permission:Create Notifikasi']], function () {
        Route::get('/notification/create', [NotificationController::class, 'create'])
            ->name('notifications.create');
        Route::post('/notification', [NotificationController::class, 'store'])
            ->name('notifications.store');
    });
    Route::group(['middleware' => ['auth', 'permission:Edit Notifikasi']], function () {
        Route::get('notification/{id}/edit', [NotificationController::class, 'edit'])
            ->name('notifications.edit');
        Route::put('notification/{id}', [NotificationController::class, 'update'])
            ->name('notifications.update');
    });
    Route::delete('notification/{id}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy')->middleware('permission:Hapus Notifikasi');
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])
        ->name('notifications.unread-count');

    Route::match(['POST', 'GET'], '/agronomidashboard',  [DashboardController::class, 'agronomi'])
        ->name('dashboard.agronomi')->middleware('permission:Dashboard Agronomi');
    Route::match(['POST', 'GET'], '/hptdashboard',  [DashboardController::class, 'hpt'])
        ->name('dashboard.hpt')->middleware('permission:Dashboard HPT');

    Route::get('/agronomipivot', [PivotController::class, 'pivotTableAgronomi'])->name('pivotTableAgronomi')
        ->middleware('permission:Pivot Agronomi');
    Route::get('/hptpivot', [PivotController::class, 'pivotTableHPT'])->name('pivotTableHPT')
        ->middleware('permission:Pivot HPT');


    Route::group(['middleware' => ['auth', 'permission:Report Agronomi']], function () {
        Route::match(['GET', 'POST'], '/agronomireport', [ReportController::class, 'agronomi'])->name('report.agronomi.index');
        Route::get('/agronomireport/excel', [AgronomiController::class, 'excel'])->name('report.agronomi.exportExcel');
    });
    Route::group(['middleware' => ['auth', 'permission:Report HPT']], function () {
        Route::match(['GET', 'POST'], '/hptreport', [ReportController::class, 'hpt'])->name('report.hpt.index');
        Route::get('/hptreport/excel', [HPTController::class, 'excel'])->name('report.hpt.exportExcel');
    });

    Route::group(['middleware' => ['auth', 'permission:Upload GPX File']], function () {
        Route::post('/upload-gpx', [GPXController::class, 'upload'])->name('upload.gpx');
        Route::get('/uploadgpx', function () {
            return view('process.upload.index', ['title' => 'Upload GPX File']);
        })->name('upload.gpx.view');
    });

    Route::group(['middleware' => ['auth', 'permission:Export KML File']], function () {
        Route::post('/export-kml', [GPXController::class, 'export'])->name('export.kml');
        Route::get('/exportkml', function () {
            return view('process.export.index', ['title' => 'Export KML File']);
        })->name('export.kml.view');
    });

    Route::group(['middleware' => ['auth', 'permission:Posting']], function () {
        Route::match(['POST', 'GET'], '/posting',  [PostController::class, 'index'])->name('process.posting');
        Route::post('/posting/submit', [PostController::class, 'posting'])->name('process.posting.submit');
        Route::post('/post-session', [PostController::class, 'postSession'])->name('postSession');
    });

    Route::group(['middleware' => ['auth', 'permission:Unposting']], function () {
        Route::match(['POST', 'GET'], '/unposting',  [UnpostController::class, 'index'])->name('process.unposting');
        Route::post('/unposting/submit', [UnpostController::class, 'unposting'])->name('process.unposting.submit');
        Route::post('/unpost-session', [UnpostController::class, 'unpostSession'])->name('unpostSession');
    });

    Route::get('/closing', [ClosingController::class, 'closing'])->name('closing')
        ->middleware('permission:Closing');
});
