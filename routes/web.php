<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GPXController;
use App\Http\Controllers\HPTController;
use App\Http\Controllers\BlokController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PivotController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UnpostController;
use App\Http\Controllers\ClosingController;
use App\Http\Controllers\MappingController;
use App\Http\Controllers\AgronomiController;
use App\Http\Controllers\PlottingController;
use App\Http\Controllers\UsernameController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\UsernameLoginController;

Route::get('/login', [UsernameLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UsernameLoginController::class, 'login'])->name('login');
Route::post('/logout', [UsernameLoginController::class, 'logout'])->name('logout');

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

    //Perusahaan
    Route::group(['middleware' => ['auth', 'permission:Company']], function () {
        Route::post('/perusahaan', [PerusahaanController::class, 'handle'])->name('master.perusahaan.handle');
        Route::get('/perusahaan', [PerusahaanController::class, 'index'])->name('master.perusahaan.index');
    });
    Route::put('perusahaan/{kd_comp}', [PerusahaanController::class, 'update'])->name('master.perusahaan.update')->middleware('permission:Edit Company');
    Route::delete('perusahaan/{kd_comp}', [PerusahaanController::class, 'destroy'])
        ->name('master.perusahaan.destroy')->middleware('permission:Hapus Company');

    //Blok
    Route::group(['middleware' => ['auth', 'permission:Blok']], function () {
        Route::get('/blok', [BlokController::class, 'index'])->name('master.blok.index');
        Route::post('/blok', [BlokController::class, 'handle'])->name('master.blok.handle');
    });
    Route::delete('blok/{kd_blok}/{kd_comp}', [BlokController::class, 'destroy'])
        ->name('master.blok.destroy')->middleware('permission:Hapus Blok');
    Route::put('blok/{kd_blok}/{kd_comp}', [BlokController::class, 'update'])
        ->name('master.blok.update')->middleware('permission:Edit Blok');

    //Plotting
    Route::group(['middleware' => ['auth', 'permission:Plotting']], function () {
        Route::post('/plotting', [PlottingController::class, 'handle'])->name('master.plotting.handle');
        Route::get('/plotting', [PlottingController::class, 'index'])->name('master.plotting.index');
    });
    Route::delete('plotting/{kd_plot}/{kd_comp}', [PlottingController::class, 'destroy'])
        ->name('master.plotting.destroy')->middleware('permission:Hapus Plotting');
    Route::put('plotting/{kd_plot}/{kd_comp}', [PlottingController::class, 'update'])
        ->name('master.plotting.update')->middleware('permission:Edit Plotting');

    //Mapping
    Route::group(['middleware' => ['auth', 'permission:Mapping']], function () {
        Route::post('/mapping/get-filtered-data', [MappingController::class, 'getFilteredData'])->name('get.filtered.data');
        Route::post('/mapping', [MappingController::class, 'handle'])->name('master.mapping.handle');
        Route::get('/mapping', [MappingController::class, 'index'])->name('master.mapping.index');
    });
    Route::delete('mapping/{kd_plotsample}/{kd_blok}/{kd_plot}/{kd_comp}', [MappingController::class, 'destroy'])
        ->name('master.mapping.destroy')->middleware('permission:Hapus Mapping');
    Route::put('mapping/{kd_plotsample}/{kd_blok}/{kd_plot}/{kd_comp}', [MappingController::class, 'update'])
        ->name('master.mapping.update')->middleware('permission:Edit Mapping');


    Route::group(['middleware' => ['auth', 'permission:Agronomi']], function () {

        Route::get('/agronomi', [AgronomiController::class, 'index'])->name('input.agronomi.index');
        Route::post('/agronomi', [AgronomiController::class, 'handle'])->name('input.agronomi.handle');
        Route::get('agronomi/show/{no_sample}/{kd_comp}/{tgltanam}', [AgronomiController::class, 'show'])
            ->name('input.agronomi.show');
        Route::post('/agronomi/get-field', [AgronomiController::class, 'getFieldByMapping'])->name('input.agronomi.getFieldByMapping');
        Route::get('/agronomi/check-data', [AgronomiController::class, 'checkData'])->name('input.agronomi.check-data');
    });
    Route::get('/agronomi/excel', [AgronomiController::class, 'excel'])
        ->name('input.agronomi.exportExcel')->middleware('permission:Excel Agronomi');
    Route::get('/agronomi/create', [AgronomiController::class, 'create'])
        ->name('input.agronomi.create')->middleware('permission:Create Agronomi');
    Route::delete('agronomi/{no_sample}/{kd_comp}/{tgltanam}', [AgronomiController::class, 'destroy'])
        ->name('input.agronomi.destroy')->middleware('permission:Hapus Agronomi');
    Route::group(['middleware' => ['auth', 'permission:Edit Agronomi']], function () {
        Route::put('agronomi/{no_sample}/{kd_comp}/{tgltanam}', [AgronomiController::class, 'update'])
            ->name('input.agronomi.update');
        Route::get('agronomi/{no_sample}/{kd_comp}/{tgltanam}/edit', [AgronomiController::class, 'edit'])
            ->name('input.agronomi.edit');
    });

    Route::group(['middleware' => ['auth', 'permission:HPT']], function () {

        Route::get('/hpt', [HPTController::class, 'index'])->name('input.hpt.index');
        Route::post('/hpt', [HPTController::class, 'handle'])->name('input.hpt.handle');
        Route::get('hpt/show/{no_sample}/{kd_comp}/{tgltanam}', [HPTController::class, 'show'])
            ->name('input.hpt.show');
        Route::post('/hpt/get-field', [HPTController::class, 'getFieldByMapping'])->name('input.hpt.getFieldByMapping');
        Route::get('/hpt/check-data', [HPTController::class, 'checkData'])->name('input.hpt.check-data');
    });
    Route::get('/hpt/excel', [HPTController::class, 'excel'])
        ->name('input.hpt.exportExcel')->middleware('permission:Excel HPT');
    Route::get('/hpt/create', [HPTController::class, 'create'])
        ->name('input.hpt.create')->middleware('permission:Create HPT');
    Route::delete('hpt/{no_sample}/{kd_comp}/{tgltanam}', [HPTController::class, 'destroy'])
        ->name('input.hpt.destroy')->middleware('permission:Hapus HPT');
    Route::group(['middleware' => ['auth', 'permission:Edit HPT']], function () {

        Route::put('hpt/{no_sample}/{kd_comp}/{tgltanam}', [HPTController::class, 'update'])
            ->name('input.hpt.update');
        Route::get('hpt/{no_sample}/{kd_comp}/{tgltanam}/edit', [HPTController::class, 'edit'])
            ->name('input.hpt.edit');
    });

    Route::group(['middleware' => ['auth', 'permission:Kelola User']], function () {

        Route::post('/username', [UsernameController::class, 'handle'])->name('master.username.handle');
        Route::get('/username', [UsernameController::class, 'index'])->name('master.username.index');
    });
    Route::get('/username/create', [UsernameController::class, 'create'])
        ->name('master.username.create')->middleware('permission:Create User');
    Route::delete('username/{usernm}/{kd_comp}', [UsernameController::class, 'destroy'])
        ->name('master.username.destroy')->middleware('permission:Hapus User');
    Route::group(['middleware' => ['auth', 'permission:Edit User']], function () {
        Route::put('username/update/{usernm}/{kd_comp}', [UsernameController::class, 'update'])
            ->name('master.username.update');
        Route::get('username/{usernm}/{kd_comp}/edit', [UsernameController::class, 'edit'])
            ->name('master.username.edit');
    });
    Route::group(['middleware' => ['auth', 'permission:Hak Akses']], function () {
        Route::put('username/access/{usernm}', [UsernameController::class, 'setaccess'])
            ->name('master.username.setaccess');
        Route::get('username/{usernm}/access', [UsernameController::class, 'access'])
            ->name('master.username.access');
    });

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
