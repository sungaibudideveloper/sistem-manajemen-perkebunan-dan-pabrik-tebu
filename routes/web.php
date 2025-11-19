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

   Route::get('utility/deploy', function () {
    // ⚠️ PENTING: Tambah authentication!
    if (request()->get('secret') !== config('app.deploy_secret')) {
        abort(403, 'Unauthorized');
    }
    
    $results = [];
    $errors = [];
    
    try {
        // 1. CEK WHOAMI
        exec('whoami 2>&1', $outWhoami, $codeWho);
        $results['user'] = implode("\n", $outWhoami);
        
        // 2. CEK WORKING DIRECTORY
        $basePath = base_path();
        $results['base_path'] = $basePath;
        
        if (!chdir($basePath)) {
            throw new Exception("Failed to change directory to: $basePath");
        }
        
        // 3. CEK GIT STATUS
        exec('git status 2>&1', $gitStatus, $gitStatusCode);
        $results['git_status'] = implode("\n", $gitStatus);
        
        if ($gitStatusCode !== 0) {
            $errors[] = "Git not available or not a git repository";
        }
        
        // 4. GIT PULL (jika git tersedia)
        if (empty($errors)) {
            exec('git pull origin main 2>&1', $gitPull, $gitPullCode);
            $results['git_pull'] = implode("\n", $gitPull);
            $results['git_pull_code'] = $gitPullCode;
            
            if ($gitPullCode !== 0) {
                $errors[] = "Git pull failed with code: $gitPullCode";
            }
        }
        
        // 5. NPM BUILD (cek dulu npm ada atau tidak)
        exec('which npm 2>&1', $npmPath, $npmCheckCode);
        
        if ($npmCheckCode === 0) {
            exec('npm run build 2>&1', $npmBuild, $npmBuildCode);
            $results['npm_build'] = implode("\n", $npmBuild);
            $results['npm_build_code'] = $npmBuildCode;
            
            if ($npmBuildCode !== 0) {
                $errors[] = "NPM build failed with code: $npmBuildCode";
            }
        } else {
            $errors[] = "NPM not found in PATH";
            $results['npm_build'] = "NPM not available";
        }
        
        // 6. ARTISAN COMMANDS (ini biasanya aman)
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize');
        
        $results['artisan_cache'] = 'All caches cleared and optimized';
        
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
    
    // 7. FORMAT OUTPUT
    $html = '<html><head><style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        h3 { color: #4ec9b0; }
        h4 { color: #569cd6; margin-top: 20px; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .error { color: #f48771; background: #3c1f1f; padding: 10px; border-left: 4px solid #f48771; margin: 10px 0; }
        .success { color: #4ec9b0; }
    </style></head><body>';
    
    if (!empty($errors)) {
        $html .= '<div class="error"><h3>⚠️ Errors:</h3>';
        foreach ($errors as $error) {
            $html .= '<p>• ' . htmlspecialchars($error) . '</p>';
        }
        $html .= '</div>';
    }
    
    $html .= '<h3 class="success">✓ Deployment Process Log</h3>';
    
    foreach ($results as $key => $value) {
        $title = ucwords(str_replace('_', ' ', $key));
        $html .= "<h4>{$title}:</h4><pre>" . htmlspecialchars($value) . "</pre>";
    }
    
    $html .= '</body></html>';
    
    return $html;
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

require __DIR__.'/pabrik.php';
require __DIR__.'/masterdata.php';
