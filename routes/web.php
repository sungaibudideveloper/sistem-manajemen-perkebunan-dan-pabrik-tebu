<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\GPXController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\UsernameLoginController;
use App\Http\Controllers\LiveChatController;
use App\Http\Controllers\React\MandorPageController;
use App\Http\Controllers\React\ApproverPageController;

/*
|--------------------------------------------------------------------------
| Web Routes - Browser & SPA Interface
|--------------------------------------------------------------------------
|
| Routes for web browser access and React/Inertia SPA.
| Uses session-based authentication with CSRF protection.
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

    // Approval routes for mobile view
    Route::prefix('input/approval')
        ->name('input.approval.')
        ->controller(\App\Http\Controllers\Input\ApprovalController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/process-rkh', 'processRKHApproval')->name('processRKH');
            Route::post('/process-lkh', 'processLKHApproval')->name('processLKH');
        });

    // Utility deployment route
    Route::get('utility/deploy', function () {
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

    // Mandor SPA routes
    Route::get('/mandor/splash', function () {
        return Inertia::render('splash-screen');
    })->name('mandor.splash');
    
    Route::get('/mandor', [MandorPageController::class, 'index'])->name('mandor.index');

    // Mandor LKH assignment and input pages
    Route::prefix('mandor/lkh')->name('mandor.lkh.')->group(function () {
        Route::get('/{lkhno}/assign', [MandorPageController::class, 'showLKHAssign'])->name('assign');
        Route::post('/{lkhno}/assign', [MandorPageController::class, 'saveLKHAssign'])->name('save-assignment');
        Route::get('/{lkhno}/input', [MandorPageController::class, 'showLKHInput'])->name('input');
        Route::get('/{lkhno}/view', [MandorPageController::class, 'showLKHView'])->name('view');
        Route::get('/{lkhno}/edit', [MandorPageController::class, 'showLKHEdit'])->name('edit');
        Route::post('/{lkhno}/input', [MandorPageController::class, 'saveLKHResults'])->name('save-results');
        Route::post('/complete-all', [MandorPageController::class, 'completeAllLKH'])->name('complete-all');
    });

    // Mandor data endpoints for SPA
    Route::prefix('mandor-data')->name('mandor.')->group(function () {
        
        // Attendance management
        Route::get('/workers', [MandorPageController::class, 'getWorkersList'])->name('workers');
        Route::get('/attendance/today', [MandorPageController::class, 'getTodayAttendance'])->name('attendance.today');
        Route::post('/attendance/process-checkin', [MandorPageController::class, 'processCheckIn'])->name('attendance.process-checkin');
        Route::post('/attendance/update-photo', [MandorPageController::class, 'updateAttendancePhoto'])->name('attendance.update-photo');
        Route::get('/attendance/rejected', [MandorPageController::class, 'getRejectedAttendance'])->name('attendance.rejected');
        Route::get('/attendance/workers-present', function(Request $request) {
            $controller = new MandorPageController();
            return $controller->getTodayAttendance($request);
        })->name('attendance.workers-present');
        
        // LKH management
        Route::get('/lkh/ready', [MandorPageController::class, 'getReadyLKH'])->name('lkh.ready');
        Route::get('/lkh/vehicle-info', [MandorPageController::class, 'getVehicleInfo'])->name('lkh.vehicle-info');
        Route::post('/lkh/save-assignment', [MandorPageController::class, 'saveLKHAssign'])->name('api.lkh.save-assignment');
        Route::post('/lkh/save-results', [MandorPageController::class, 'saveLKHResults'])->name('api.lkh.save-results');
        
        Route::get('/lkh/{lkhno}/detail', function($lkhno) {
            return response()->json([
                'lkh_detail' => [],
                'message' => 'LKH detail endpoint - implementation needed'
            ]);
        })->name('lkh.detail');
        
        Route::get('/lkh/{lkhno}/materials', function($lkhno) {
            return response()->json([
                'lkh_materials' => [],
                'message' => 'LKH materials endpoint - implementation needed'
            ]);
        })->name('lkh.materials');
        
        Route::post('/lkh/{lkhno}/update-status', function($lkhno, Request $request) {
            $status = $request->input('status');
            return response()->json([
                'success' => true,
                'message' => "LKH status updated to {$status}",
                'lkhno' => $lkhno
            ]);
        })->name('lkh.update-status');
        
        // Material management
        Route::get('/materials/available', [MandorPageController::class, 'getAvailableMaterials'])->name('materials.available');
        Route::post('/materials/save-returns', [MandorPageController::class, 'saveMaterialReturns'])->name('materials.save-returns');
        Route::post('/materials/confirm-pickup', [MandorPageController::class, 'confirmMaterialPickup'])->name('materials.confirm-pickup');
        
        // Data synchronization
        Route::post('/sync-offline-data', [MandorPageController::class, 'syncOfflineData'])->name('sync-offline-data');
        
        // Utility endpoints
        Route::post('/bulk-save', function(Request $request) {
            $operations = $request->input('operations', []);
            $results = [];
            
            foreach ($operations as $operation) {
                $results[] = [
                    'type' => $operation['type'],
                    'id' => $operation['id'] ?? null,
                    'status' => 'processed'
                ];
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk operations processed',
                'results' => $results
            ]);
        })->name('bulk-save');

        Route::get('/server-time', function() {
            return response()->json([
                'timestamp' => now()->toIso8601String(),
                'formatted' => now()->format('d/m/Y, H:i:s'),
                'timezone' => config('app.timezone')
            ]);
        })->name('server-time');
    });

    // Restricted mandor routes with permission checks
    Route::prefix('mandor')->middleware('check.mandor.permission')->group(function () {
        Route::get('/restricted-data', function() {
            return response()->json(['message' => 'Restricted mandor data']);
        })->name('mandor.restricted-data');
    });

    // Approver main dashboard
    Route::get('/approver', [ApproverPageController::class, 'index'])->name('approver.index');
    
    // Approver data endpoints for SPA
    Route::prefix('approver-data')->name('approver.')->group(function () {
        Route::get('/dashboard/stats', [ApproverPageController::class, 'getDashboardStats'])->name('dashboard.stats');
        Route::get('/attendance/pending', [ApproverPageController::class, 'getPendingAttendance'])->name('attendance.pending');
        Route::get('/mandors/pending', [ApproverPageController::class, 'getMandorListWithPending'])->name('mandors.pending');
        Route::post('/attendance/approve', [ApproverPageController::class, 'approveAttendance'])->name('attendance.approve');
        Route::post('/attendance/reject', [ApproverPageController::class, 'rejectAttendance'])->name('attendance.reject');
        Route::get('/attendance/history', [ApproverPageController::class, 'getAttendanceHistory'])->name('attendance.history');
    });
});

require __DIR__.'/pabrik.php';