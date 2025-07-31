<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\GPXController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PivotController;
use App\Http\Controllers\UnpostController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\UsernameLoginController;
use App\Http\Controllers\LiveChatController;
use App\Http\Controllers\React\MandorPageController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [UsernameLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UsernameLoginController::class, 'login'])->name('login.post');
Route::post('/logout', [UsernameLoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes - Require Authentication
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth'], function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard & Home Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/set-session', [HomeController::class, 'setSession'])->name('setSession');

    /*
    |--------------------------------------------------------------------------
    | Live Chat Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/chat/send', [LiveChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages', [LiveChatController::class, 'getMessages'])->name('chat.messages');

    /*
    |--------------------------------------------------------------------------
    | Notification Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/notification', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    
    // Notification Management (with permissions)
    Route::group(['middleware' => 'permission:Create Notifikasi'], function () {
        Route::get('/notification/create', [NotificationController::class, 'create'])->name('notifications.create');
        Route::post('/notification', [NotificationController::class, 'store'])->name('notifications.store');
    });
    
    Route::group(['middleware' => 'permission:Edit Notifikasi'], function () {
        Route::get('notification/{id}/edit', [NotificationController::class, 'edit'])->name('notifications.edit');
        Route::put('notification/{id}', [NotificationController::class, 'update'])->name('notifications.update');
    });
    
    Route::delete('notification/{id}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy')
        ->middleware('permission:Hapus Notifikasi');

    /*
    |--------------------------------------------------------------------------
    | GPX File Management Routes
    |--------------------------------------------------------------------------
    */
    Route::group(['middleware' => 'permission:Upload GPX File'], function () {
        Route::get('/uploadgpx', function () {
            return view('process.upload.index', ['title' => 'Upload GPX File']);
        })->name('upload.gpx.view');
        Route::post('/upload-gpx', [GPXController::class, 'upload'])->name('upload.gpx');
    });

    Route::group(['middleware' => 'permission:Export KML File'], function () {
        Route::get('/exportkml', function () {
            return view('process.export.index', ['title' => 'Export KML File']);
        })->name('export.kml.view');
        Route::post('/export-kml', [GPXController::class, 'export'])->name('export.kml');
    });

    /*
    |--------------------------------------------------------------------------
    | Mandor SPA Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/mandor/splash', function () {
        return Inertia::render('splash-screen');
    })->name('mandor.splash');
    
    // Main SPA entry point
    Route::get('/mandor', [MandorPageController::class, 'index'])->name('mandor.index');
    
    // API endpoints

    Route::prefix('api/mandor')->group(function () {
        Route::post('/attendance/check-in', [MandorPageController::class, 'checkIn'])->name('mandor.checkin');
        Route::post('/attendance/check-out', [MandorPageController::class, 'checkOut'])->name('mandor.checkout');
        Route::get('/attendance/data', [MandorPageController::class, 'getAttendanceData'])->name('mandor.attendance.data');
        Route::get('/field-activities', [MandorPageController::class, 'getFieldActivities'])->name('mandor.field.activities');
        
        // New attendance routes
        Route::get('/workers', [MandorPageController::class, 'getWorkersList'])->name('mandor.workers');
        Route::get('/attendance/today', [MandorPageController::class, 'getTodayAttendance'])->name('mandor.attendance.today');
        Route::post('/attendance/process-checkin', [MandorPageController::class, 'processCheckIn'])->name('mandor.attendance.process-checkin');
    });

});