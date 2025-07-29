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

Route::get('/login', [UsernameLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UsernameLoginController::class, 'login'])->name('login.post');
Route::any('/logout', [UsernameLoginController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/',  [HomeController::class, 'index'])
        ->name('home');
    Route::post('/set-session', [HomeController::class, 'setSession'])->name('setSession');

    Route::post('/chat/send', [LiveChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages', [LiveChatController::class, 'getMessages'])->name('chat.messages');

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

Route::get('/test-permissions', function() {
    $user = auth()->user();
    if (!$user) {
        return 'Not authenticated';
    }
    
    $permissions = json_decode($user->permissions ?? '[]', true);
    
    // Cari permission Dashboard
    $results = [];
    $searchTerms = ['Dashboard Agronomi', 'Dashboard HPT'];
    
    foreach ($searchTerms as $search) {
        $found = false;
        $similar = [];
        
        foreach ($permissions as $index => $perm) {
            // Exact match
            if ($perm === $search) {
                $found = true;
                $results[$search] = [
                    'status' => 'FOUND',
                    'index' => $index,
                    'value' => $perm
                ];
                break;
            }
            
            // Similar match
            if (stripos($perm, 'dashboard') !== false && 
                stripos($perm, explode(' ', $search)[1]) !== false) {
                $similar[] = [
                    'index' => $index,
                    'value' => $perm,
                    'hex' => bin2hex($perm)
                ];
            }
        }
        
        if (!$found) {
            $results[$search] = [
                'status' => 'NOT FOUND',
                'similar' => $similar
            ];
        }
    }
    
    // Get all dashboard related permissions
    $dashboardPerms = [];
    foreach ($permissions as $idx => $perm) {
        if (stripos($perm, 'dashboard') !== false) {
            $dashboardPerms[] = [
                'index' => $idx,
                'value' => $perm,
                'length' => strlen($perm),
                'hex' => bin2hex($perm)
            ];
        }
    }
    
    return response()->json([
        'user' => $user->name,
        'total_permissions' => count($permissions),
        'search_results' => $results,
        'all_dashboard_permissions' => $dashboardPerms,
        'raw_permissions' => $permissions
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
})->middleware('auth');

// Test dengan middleware
Route::get('/test-dashboard-with-middleware', function() {
    return 'Success! You have Dashboard Agronomi permission';
})->middleware(['auth', 'permission:Dashboard Agronomi']);

// Test direct call
Route::get('/test-dashboard-agronomi-call', function() {
    return app(\App\Http\Controllers\Dashboard\DashboardController::class)->agronomi();
})->middleware('auth');





Route::get('/mandor/dashboard', function () {
    return Inertia::render('dashboard-mandor', [
        'title' => 'Mandor Dashboard',
        'user' => auth()->user()
    ]);
})->middleware('auth')->name('mandor.dashboard');

// Route mandor lainnya juga cukup middleware auth saja
Route::prefix('mandor')->name('mandor.')->middleware('auth')->group(function () {
    // Route::get('/absensi', [MandorController::class, 'absensi'])->name('absensi');
    // Route::get('/field-data', [MandorController::class, 'fieldData'])->name('field-data');
});


Route::get('/manifest.json', function () {
    // Deteksi environment
    $isProduction = !in_array(request()->getHost(), ['localhost', '127.0.0.1']);
    $basePath = $isProduction ? '' : '/tebu/public';
    
    $manifest = [
        'name' => 'SB Tebu App',
        'short_name' => 'SB Tebu',
        'description' => 'Aplikasi untuk absen dan data collection tenaga kerja',
        'start_url' => $basePath . '/',
        'display' => 'standalone',
        'background_color' => '#ffffff',
        'theme_color' => '#153B50',
        'orientation' => 'portrait-primary',
        'scope' => $basePath . '/',
        'icons' => [
            [
                'src' => $basePath . '/img/icon-sb-tebu-circle.png',
                'sizes' => '1024x1024',
                'type' => 'image/png',
                'purpose' => 'maskable any'
            ],
            [
                'src' => $basePath . '/img/icon-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png'
            ],
            [
                'src' => $basePath . '/img/icon-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png'
            ]
        ]
    ];
    
    return response()->json($manifest)
        ->header('Content-Type', 'application/json')
        ->header('Cache-Control', 'public, max-age=3600');
});