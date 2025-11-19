<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GPXController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\UsernameLoginController;
use App\Http\Controllers\LiveChatController;

/*
|--------------------------------------------------------------------------
| Web Routes - Traditional Blade Views
|--------------------------------------------------------------------------
|
| Routes for traditional server-rendered Blade views and forms
| Authentication: Session-based
|
*/

// Authentication routes
Route::get('/login', [UsernameLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UsernameLoginController::class, 'login'])->name('login.post');
Route::post('/logout', [UsernameLoginController::class, 'logout'])->name('logout');

// Protected routes - require authentication
Route::group(['middleware' => ['auth', 'mandor.access']], function () {

    // Dashboard and home
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/set-session', [HomeController::class, 'setSession'])->name('setSession');

    // Live chat
    Route::post('/chat/send', [LiveChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages', [LiveChatController::class, 'getMessages'])->name('chat.messages');

    // Approval routes for mobile view (traditional Blade)
    Route::prefix('input/approval')
        ->name('input.approval.')
        ->controller(\App\Http\Controllers\Input\ApprovalController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/process-rkh', 'processRKHApproval')->name('processRKH');
            Route::post('/process-lkh', 'processLKHApproval')->name('processLKH');
        });
        
    // User notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/dropdown-data', [NotificationController::class, 'getDropdownData'])->name('dropdown-data');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    // Admin notification management routes
    Route::middleware('permission:Admin')->group(function () {
        Route::get('/notifications/admin', [NotificationController::class, 'adminIndex'])->name('notifications.admin.index');
    });

    Route::middleware('permission:Create Notifikasi')->group(function () {
        Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
        Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    });

    Route::middleware('permission:Edit Notifikasi')->group(function () {
        Route::get('/notifications/{id}/edit', [NotificationController::class, 'edit'])->name('notifications.edit');
        Route::put('/notifications/{id}', [NotificationController::class, 'update'])->name('notifications.update');
    });

    Route::middleware('permission:Hapus Notifikasi')->group(function () {
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // GPX file management routes
    Route::middleware('permission:Upload GPX File')->group(function () {
        Route::get('/uploadgpx', function () {
            return view('process.upload.index', ['title' => 'Upload GPX File']);
        })->name('upload.gpx.view');
        Route::post('/upload-gpx', [GPXController::class, 'upload'])->name('upload.gpx');
    });

    Route::middleware('permission:Export KML File')->group(function () {
        Route::get('/exportkml', function () {
            return view('process.export.index', ['title' => 'Export KML File']);
        })->name('export.kml.view');
        Route::post('/export-kml', [GPXController::class, 'export'])->name('export.kml');
    });
});

Route::get('utility/deploy', function () {
    return response()->stream(function () {
        echo '<pre style="background:#000;color:#0f0;padding:20px;white-space:pre-wrap;font-family:monospace;">';
        echo str_pad('', 4096) . "\n"; // Browser buffer padding
        flush();
        
        echo "=== DEPLOYMENT START ===\n\n";
        flush();
        
        try {
            chdir(base_path());
            
            // 1. Git safe directory
            echo "1. Git safe directory...\n";
            flush();
            shell_exec('git config --global --add safe.directory "' . base_path() . '" 2>&1');
            echo "   ✓ OK\n\n";
            flush();
            
            // 2. Stash
            echo "2. Stashing local changes...\n";
            flush();
            $stashResult = shell_exec('git stash 2>&1');
            echo "   " . htmlspecialchars($stashResult) . "\n";
            flush();
            
            // 3. Git pull
            echo "3. Git pull...\n";
            flush();
            $pullResult = shell_exec('git pull origin main 2>&1');
            echo "   " . htmlspecialchars($pullResult) . "\n";
            flush();
            
            // 4. Pop stash
            echo "4. Restoring local changes...\n";
            flush();
            $popResult = shell_exec('git stash pop 2>&1');
            echo "   " . htmlspecialchars($popResult) . "\n";
            flush();
            
            // 5. Clear cache
            echo "5. Clearing cache...\n";
            flush();
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            echo "   ✓ All caches cleared\n\n";
            flush();
            
            // 6. Current commit
            echo "6. Current commit:\n";
            $commit = shell_exec('git log -1 --oneline 2>&1');
            echo "   " . htmlspecialchars($commit) . "\n\n";
            flush();
            
            echo "=== ✅ SUCCESS ===\n";
            
        } catch (Exception $e) {
            echo "\n=== ❌ ERROR ===\n";
            echo htmlspecialchars($e->getMessage()) . "\n";
        }
        
        echo '</pre>';
        flush();
        
    }, 200, [
        'Content-Type' => 'text/html; charset=UTF-8',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no',
    ]);
});
require __DIR__.'/pabrik.php';
require __DIR__.'/masterdata.php';
