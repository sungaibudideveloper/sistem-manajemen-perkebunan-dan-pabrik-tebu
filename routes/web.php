<?php

// routes\web.php

use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\GPXController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\UsernameLoginController;
use App\Http\Controllers\LiveChatController;

// ============================================================================
// PUBLIC ROUTES (No Authentication)
// ============================================================================
Route::get('/login', [UsernameLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UsernameLoginController::class, 'login'])->name('login.post');
Route::post('/logout', [UsernameLoginController::class, 'logout'])->name('logout');

// ============================================================================
// AUTHENTICATED ROUTES
// ============================================================================
Route::middleware(['auth', 'mandor.access'])->group(function () {

    // ============================================================================
    // HOME & DASHBOARD
    // ============================================================================
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/set-session', [HomeController::class, 'setSession'])->name('setSession');

    // ============================================================================
    // LIVE CHAT
    // ============================================================================
    Route::post('/chat/send', [LiveChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages', [LiveChatController::class, 'getMessages'])->name('chat.messages');

    // ============================================================================
    // APPROVAL (Mobile View - Traditional Blade)
    // ============================================================================
    Route::middleware('permission:transaction.approval.view')->group(function () {
        Route::prefix('transaction/approval')->name('transaction.approval.')->controller(\App\Http\Controllers\Transaction\ApprovalController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/process-rkh', 'processRKHApproval')->name('processRKH');
            Route::post('/process-lkh', 'processLKHApproval')->name('processLKH');
            Route::post('/process-other', 'processOtherApproval')->name('processOther');
        });
    });

    // ============================================================================
    // GPX/KML FILE MANAGEMENT
    // ============================================================================
    Route::middleware('permission:process.uploadgpx.view')->group(function () {
        Route::get('/uploadgpx', function () {
            return view('process.upload.index', ['title' => 'Upload GPX File']);
        })->name('upload.gpx.view');
        Route::post('/upload-gpx', [GPXController::class, 'upload'])->name('upload.gpx');
    });

    Route::middleware('permission:process.exportkml.view')->group(function () {
        Route::get('/exportkml', function () {
            return view('process.export.index', ['title' => 'Export KML File']);
        })->name('export.kml.view');
        Route::post('/export-kml', [GPXController::class, 'export'])->name('export.kml');
    });

    // ============================================================================
    // UTILITY DEPLOYMENT (Development Only - Remove in Production!)
    // ============================================================================
    Route::get('utility/deploy', function () {
        if (config('app.env') !== 'local') {
            abort(403, 'Not allowed in production');
        }

        $output = [];
        $results = [];

        chdir(base_path());
        exec('git pull origin main 2>&1', $output);
        $results['git_pull'] = implode("\n", $output);

        $output = [];
        exec('npm run build 2>&1', $output);
        $results['npm_build'] = implode("\n", $output);

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        $results['cache'] = 'All caches cleared';

        $html = '<h3>Deployment Completed!</h3>';
        $html .= '<h4>1. Git Pull:</h4><pre>' . $results['git_pull'] . '</pre>';
        $html .= '<h4>2. NPM Build:</h4><pre>' . $results['npm_build'] . '</pre>';
        $html .= '<h4>3. Cache Cleared:</h4><pre>' . $results['cache'] . '</pre>';

        return $html;
    })->name('utility.deploy');

});