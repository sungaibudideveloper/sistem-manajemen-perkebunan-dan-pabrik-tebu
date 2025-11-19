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
    set_time_limit(120);
    
    if (request()->get('secret') !== config('app.deploy_secret')) {
        abort(403, 'Unauthorized');
    }
    
    $results = [];
    $errors = [];
    $startTime = microtime(true);
    
    // Deteksi OS
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    $results['os'] = PHP_OS;
    
    try {
        // 1. Basic Info - Windows compatible
        if ($isWindows) {
            $results['user'] = trim(shell_exec('echo %USERNAME%'));
            $results['working_dir'] = trim(shell_exec('cd'));
        } else {
            $results['user'] = trim(shell_exec('whoami'));
            $results['working_dir'] = trim(shell_exec('pwd'));
        }
        
        $results['start_time'] = date('Y-m-d H:i:s');
        
        // 2. Change to base path
        $basePath = base_path();
        if (!chdir($basePath)) {
            throw new Exception("Cannot change to directory: $basePath");
        }
        
        if ($isWindows) {
            $results['changed_to'] = trim(shell_exec('cd'));
        } else {
            $results['changed_to'] = trim(shell_exec('pwd'));
        }
        
        // 3. Git Status
        exec('git status --porcelain 2>&1', $gitStatusOutput, $gitStatusCode);
        $results['git_status'] = empty($gitStatusOutput) ? 'Clean working directory' : implode("\n", $gitStatusOutput);
        $results['git_status_code'] = $gitStatusCode;
        
        if ($gitStatusCode !== 0) {
            $errors[] = "Git status failed with code: $gitStatusCode";
        }
        
        // 4. Git Fetch
        exec('git fetch origin main 2>&1', $gitFetchOutput, $gitFetchCode);
        $results['git_fetch'] = implode("\n", $gitFetchOutput);
        $results['git_fetch_code'] = $gitFetchCode;
        
        // 5. Git Pull - Windows version (NO timeout command)
        exec('git pull origin main --no-edit --ff-only 2>&1', $gitPullOutput, $gitPullCode);
        
        $results['git_pull'] = implode("\n", $gitPullOutput);
        $results['git_pull_exit_code'] = $gitPullCode;
        
        if ($gitPullCode !== 0) {
            $errors[] = "Git pull failed with exit code: $gitPullCode";
        }
        // 7. NPM Build (optional - uncomment jika perlu)
        exec('npm run build 2>&1', $npmBuildOutput, $npmBuildCode);
        $results['npm_build'] = implode("\n", $npmBuildOutput);
        
        
        // 9. Artisan Clear Cache
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize');
        
        $results['artisan'] = 'All caches cleared and optimized';
        
        // 10. Current Commit Info
        $results['current_commit'] = trim(shell_exec('git log -1 --pretty=format:"%h - %s (%cr)" 2>&1'));
        
        // 11. Latest commits (last 5)
        exec('git log -5 --pretty=format:"%h - %s (%cr by %an)" 2>&1', $gitLogOutput);
        $results['recent_commits'] = implode("\n", $gitLogOutput);
        
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
    
    $executionTime = round(microtime(true) - $startTime, 2);
    
    // HTML Output dengan style yang bagus
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        h2 { 
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        h3 { 
            color: #764ba2;
            margin-top: 25px;
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        pre { 
            background: #f5f7fa;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            border-left: 4px solid #667eea;
            font-family: "Consolas", "Monaco", monospace;
            font-size: 0.9em;
            line-height: 1.5;
        }
        .error { 
            background: #fee;
            color: #c33;
            padding: 15px;
            border-left: 4px solid #c33;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success { 
            background: #efe;
            color: #3a3;
            padding: 15px;
            border-left: 4px solid #3a3;
            margin: 20px 0;
            border-radius: 4px;
        }
        .metrics { 
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .metric { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .footer {
            background: #f5f7fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 0.9em;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
            margin-left: 10px;
        }
        .badge-windows {
            background: #0078d4;
            color: white;
        }
        .badge-linux {
            background: #f7931e;
            color: white;
        }
    </style></head><body>';
    
    $html .= '<div class="container">';
    $html .= '<div class="header">';
    $html .= '<h1>🚀 Deployment Dashboard</h1>';
    $html .= '<p>Sungai Budi Group - Tebu Application</p>';
    $html .= '</div>';
    
    $html .= '<div class="content">';
    
    // Errors section
    if (!empty($errors)) {
        $html .= '<div class="error"><h3>⚠️ Errors Detected:</h3><ul style="margin-left: 20px; margin-top: 10px;">';
        foreach ($errors as $error) {
            $html .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $html .= '</ul></div>';
    } else {
        $html .= '<div class="success"><strong>✅ Deployment completed successfully!</strong></div>';
    }
    
    // Metrics
    $osBadge = $isWindows ? '<span class="badge badge-windows">Windows</span>' : '<span class="badge badge-linux">Linux</span>';
    
    $html .= '<div class="metrics">';
    $html .= '<div class="metric">⏱️ Time: ' . $executionTime . 's</div>';
    $html .= '<div class="metric">👤 User: ' . htmlspecialchars($results['user']) . '</div>';
    $html .= '<div class="metric">💻 OS: ' . htmlspecialchars($results['os']) . ' ' . $osBadge . '</div>';
    $html .= '<div class="metric">📅 ' . $results['start_time'] . '</div>';
    $html .= '</div>';
    
    // Results
    $html .= '<h2>📋 Deployment Details</h2>';
    
    $priorityKeys = ['git_pull', 'current_commit', 'recent_commits', 'git_status', 'artisan'];
    $otherKeys = array_diff(array_keys($results), $priorityKeys, ['user', 'start_time', 'os']);
    $orderedKeys = array_merge($priorityKeys, $otherKeys);
    
    foreach ($orderedKeys as $key) {
        if (!isset($results[$key]) || in_array($key, ['user', 'start_time', 'os'])) continue;
        
        $title = ucwords(str_replace('_', ' ', $key));
        $icon = match($key) {
            'git_pull' => '📥',
            'git_fetch' => '🔄',
            'git_status' => '📊',
            'current_commit' => '🎯',
            'recent_commits' => '📜',
            'artisan' => '🧹',
            'npm_build' => '📦',
            'composer' => '🎵',
            default => '📄'
        };
        
        $html .= '<h3>' . $icon . ' ' . htmlspecialchars($title) . '</h3>';
        $html .= '<pre>' . htmlspecialchars($results[$key]) . '</pre>';
    }
    
    $html .= '</div>';
    
    $html .= '<div class="footer">';
    $html .= '<p><strong>Dazytech Solutions</strong> - Professional Software Development</p>';
    $html .= '<p style="margin-top: 5px; font-size: 0.85em;">Deployment automation system v1.0</p>';
    $html .= '</div>';
    
    $html .= '</div></body></html>';
    
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
