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

    //company
    Route::group(['middleware' => ['auth', 'permission:Company']], function () {
        Route::post('/company', [companyController::class, 'handle'])->name('master.company.handle');
        Route::get('/company', [companyController::class, 'index'])->name('master.company.index');
    });
    Route::put('company/{companycode}', [companyController::class, 'update'])->name('master.company.update')->middleware('permission:Edit Company');
    Route::delete('company/{companycode}', [companyController::class, 'destroy'])
        ->name('master.company.destroy')->middleware('permission:Hapus Company');

    //Blok
    Route::group(['middleware' => ['auth', 'permission:Blok']], function () {
        Route::get('/blok', [BlokController::class, 'index'])->name('master.blok.index');
        Route::post('/blok', [BlokController::class, 'handle'])->name('master.blok.handle');
    });
    Route::delete('blok/{blok}/{companycode}', [BlokController::class, 'destroy'])
        ->name('master.blok.destroy')->middleware('permission:Hapus Blok');
    Route::put('blok/{blok}/{companycode}', [BlokController::class, 'update'])
        ->name('master.blok.update')->middleware('permission:Edit Blok');

    //Plotting
    Route::group(['middleware' => ['auth', 'permission:Plotting']], function () {
        Route::post('/plotting', [PlottingController::class, 'handle'])->name('master.plotting.handle');
        Route::get('/plotting', [PlottingController::class, 'index'])->name('master.plotting.index');
    });
    Route::delete('plotting/{plot}/{companycode}', [PlottingController::class, 'destroy'])
        ->name('master.plotting.destroy')->middleware('permission:Hapus Plotting');
    Route::put('plotting/{plot}/{companycode}', [PlottingController::class, 'update'])
        ->name('master.plotting.update')->middleware('permission:Edit Plotting');

    //Mapping
    Route::group(['middleware' => ['auth', 'permission:Mapping']], function () {
        Route::post('/mapping/get-filtered-data', [MappingController::class, 'getFilteredData'])->name('get.filtered.data');
        Route::post('/mapping', [MappingController::class, 'handle'])->name('master.mapping.handle');
        Route::get('/mapping', [MappingController::class, 'index'])->name('master.mapping.index');
    });
    Route::delete('mapping/{plotcodesample}/{blok}/{plot}/{companycode}', [MappingController::class, 'destroy'])
        ->name('master.mapping.destroy')->middleware('permission:Hapus Mapping');
    Route::put('mapping/{plotcodesample}/{blok}/{plot}/{companycode}', [MappingController::class, 'update'])
        ->name('master.mapping.update')->middleware('permission:Edit Mapping');


    Route::group(['middleware' => ['auth', 'permission:Agronomi']], function () {

        Route::get('/agronomi', [AgronomiController::class, 'index'])->name('input.agronomi.index');
        Route::post('/agronomi', [AgronomiController::class, 'handle'])->name('input.agronomi.handle');
        Route::get('agronomi/show/{no_sample}/{companycode}/{tanggaltanam}', [AgronomiController::class, 'show'])
            ->name('input.agronomi.show');
        Route::post('/agronomi/get-field', [AgronomiController::class, 'getFieldByMapping'])->name('input.agronomi.getFieldByMapping');
        Route::get('/agronomi/check-data', [AgronomiController::class, 'checkData'])->name('input.agronomi.check-data');
    });
    Route::get('/agronomi/excel', [AgronomiController::class, 'excel'])
        ->name('input.agronomi.exportExcel')->middleware('permission:Excel Agronomi');
    Route::get('/agronomi/create', [AgronomiController::class, 'create'])
        ->name('input.agronomi.create')->middleware('permission:Create Agronomi');
    Route::delete('agronomi/{no_sample}/{companycode}/{tanggaltanam}', [AgronomiController::class, 'destroy'])
        ->name('input.agronomi.destroy')->middleware('permission:Hapus Agronomi');
    Route::group(['middleware' => ['auth', 'permission:Edit Agronomi']], function () {
        Route::put('agronomi/{no_sample}/{companycode}/{tanggaltanam}', [AgronomiController::class, 'update'])
            ->name('input.agronomi.update');
        Route::get('agronomi/{no_sample}/{companycode}/{tanggaltanam}/edit', [AgronomiController::class, 'edit'])
            ->name('input.agronomi.edit');
    });

    Route::group(['middleware' => ['auth', 'permission:HPT']], function () {

        Route::get('/hpt', [HPTController::class, 'index'])->name('input.hpt.index');
        Route::post('/hpt', [HPTController::class, 'handle'])->name('input.hpt.handle');
        Route::get('hpt/show/{no_sample}/{companycode}/{tanggaltanam}', [HPTController::class, 'show'])
            ->name('input.hpt.show');
        Route::post('/hpt/get-field', [HPTController::class, 'getFieldByMapping'])->name('input.hpt.getFieldByMapping');
        Route::get('/hpt/check-data', [HPTController::class, 'checkData'])->name('input.hpt.check-data');
    });
    Route::get('/hpt/excel', [HPTController::class, 'excel'])
        ->name('input.hpt.exportExcel')->middleware('permission:Excel HPT');
    Route::get('/hpt/create', [HPTController::class, 'create'])
        ->name('input.hpt.create')->middleware('permission:Create HPT');
    Route::delete('hpt/{no_sample}/{companycode}/{tanggaltanam}', [HPTController::class, 'destroy'])
        ->name('input.hpt.destroy')->middleware('permission:Hapus HPT');
    Route::group(['middleware' => ['auth', 'permission:Edit HPT']], function () {

        Route::put('hpt/{no_sample}/{companycode}/{tanggaltanam}', [HPTController::class, 'update'])
            ->name('input.hpt.update');
        Route::get('hpt/{no_sample}/{companycode}/{tanggaltanam}/edit', [HPTController::class, 'edit'])
            ->name('input.hpt.edit');
    });

    Route::group(['middleware' => ['auth', 'permission:Kelola User']], function () {

        Route::post('/username', [UsernameController::class, 'handle'])->name('master.username.handle');
        Route::get('/username', [UsernameController::class, 'index'])->name('master.username.index');
    });
    Route::get('/username/create', [UsernameController::class, 'create'])
        ->name('master.username.create')->middleware('permission:Create User');
    Route::delete('username/{userid}/{companycode}', [UsernameController::class, 'destroy'])
        ->name('master.username.destroy')->middleware('permission:Hapus User');
    Route::group(['middleware' => ['auth', 'permission:Edit User']], function () {
        Route::put('username/update/{userid}/{companycode}', [UsernameController::class, 'update'])
            ->name('master.username.update');
        Route::get('username/{userid}/{companycode}/edit', [UsernameController::class, 'edit'])
            ->name('master.username.edit');
    });
    Route::group(['middleware' => ['auth', 'permission:Hak Akses']], function () {
        Route::put('username/access/{userid}', [UsernameController::class, 'setaccess'])
            ->name('master.username.setaccess');
        Route::get('username/{userid}/access', [UsernameController::class, 'access'])
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
