<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GPXController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PivotController;
use App\Http\Controllers\UnpostController;
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
});
