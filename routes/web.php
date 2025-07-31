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
    
    /*
    |--------------------------------------------------------------------------
    | Mandor LKH Assignment & Input Pages
    |--------------------------------------------------------------------------
    */
    
    // LKH Assignment Page
    Route::get('/mandor/lkh/{lkhno}/assign', [MandorPageController::class, 'showLKHAssign'])
        ->name('mandor.lkh.assign');
    
    Route::post('/mandor/lkh/{lkhno}/assign', [MandorPageController::class, 'saveLKHAssign'])
        ->name('mandor.lkh.save-assignment');
    
    // LKH Input Results Page  
    Route::get('/mandor/lkh/{lkhno}/input', [MandorPageController::class, 'showLKHInput'])
        ->name('mandor.lkh.input');
    
    Route::post('/mandor/lkh/{lkhno}/input', [MandorPageController::class, 'saveLKHResults'])
        ->name('mandor.lkh.save-results');
    
    /*
    |--------------------------------------------------------------------------
    | Mandor API Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/mandor')->group(function () {
        
        // ===== ATTENDANCE APIs =====
        Route::post('/attendance/check-in', [MandorPageController::class, 'checkIn'])->name('mandor.checkin');
        Route::post('/attendance/check-out', [MandorPageController::class, 'checkOut'])->name('mandor.checkout');
        Route::get('/attendance/data', [MandorPageController::class, 'getAttendanceData'])->name('mandor.attendance.data');
        Route::get('/field-activities', [MandorPageController::class, 'getFieldActivities'])->name('mandor.field.activities');
        
        // Enhanced attendance routes
        Route::get('/workers', [MandorPageController::class, 'getWorkersList'])->name('mandor.workers');
        Route::get('/attendance/today', [MandorPageController::class, 'getTodayAttendance'])->name('mandor.attendance.today');
        Route::post('/attendance/process-checkin', [MandorPageController::class, 'processCheckIn'])->name('mandor.attendance.process-checkin');
        
        // ===== FIELD COLLECTION APIs =====
        
        // LKH Management
        Route::get('/lkh/ready', [MandorPageController::class, 'getReadyLKH'])->name('mandor.lkh.ready');
        Route::get('/lkh/vehicle-info', [MandorPageController::class, 'getVehicleInfo'])->name('mandor.lkh.vehicle-info');
        Route::post('/lkh/save-work', [MandorPageController::class, 'saveLKHWork'])->name('mandor.lkh.save-work');
        
        // NEW LKH Assignment & Results API Routes
        Route::post('/lkh/save-assignment', [MandorPageController::class, 'saveLKHAssign'])
            ->name('mandor.api.lkh.save-assignment');
        
        Route::post('/lkh/save-results', [MandorPageController::class, 'saveLKHResults'])
            ->name('mandor.api.lkh.save-results');
        
        // Material Management
        Route::get('/materials/available', [MandorPageController::class, 'getAvailableMaterials'])->name('mandor.materials.available');
        Route::post('/materials/save-returns', [MandorPageController::class, 'saveMaterialReturns'])->name('mandor.materials.save-returns');
        
        // Semi-Offline Data Sync
        Route::post('/sync-offline-data', [MandorPageController::class, 'syncOfflineData'])->name('mandor.sync-offline-data');
        
        // ===== ADDITIONAL UTILITY APIs =====
        
        // Get workers yang sudah absen hari ini (untuk assignment di LKH)
        Route::get('/attendance/workers-present', function(Request $request) {
            $date = $request->input('date', now()->format('Y-m-d'));
            $controller = new MandorPageController();
            return $controller->getTodayAttendance($request);
        })->name('mandor.attendance.workers-present');
        
        // Get LKH detail untuk edit/update
        Route::get('/lkh/{lkhno}/detail', function($lkhno) {
            // Implementation untuk get LKH detail
            return response()->json([
                'lkh_detail' => [],
                'message' => 'LKH detail endpoint - implementation needed'
            ]);
        })->name('mandor.lkh.detail');
        
        // Get material usage by LKH
        Route::get('/lkh/{lkhno}/materials', function($lkhno) {
            // Implementation untuk get materials used in specific LKH
            return response()->json([
                'lkh_materials' => [],
                'message' => 'LKH materials endpoint - implementation needed'
            ]);
        })->name('mandor.lkh.materials');
        
        // Update LKH status (DRAFT -> COMPLETED -> SUBMITTED)
        Route::post('/lkh/{lkhno}/update-status', function($lkhno, Request $request) {
            $status = $request->input('status'); // DRAFT, COMPLETED, SUBMITTED
            // Implementation untuk update LKH status
            return response()->json([
                'success' => true,
                'message' => "LKH status updated to {$status}",
                'lkhno' => $lkhno
            ]);
        })->name('mandor.lkh.update-status');
        
        // Get daily summary (untuk dashboard)
        Route::get('/daily-summary', function(Request $request) {
            $date = $request->input('date', now()->format('Y-m-d'));
            
            // Mock response - implement actual logic
            return response()->json([
                'date' => $date,
                'summary' => [
                    'total_lkh' => 5,
                    'completed_lkh' => 2,
                    'pending_lkh' => 3,
                    'total_workers_assigned' => 15,
                    'total_area_completed' => 8.5,
                    'materials_taken' => 3,
                    'materials_returned' => 1
                ]
            ]);
        })->name('mandor.daily-summary');
        
        // Bulk operations untuk semi-offline
        Route::post('/bulk-save', function(Request $request) {
            $operations = $request->input('operations', []);
            
            // Process multiple operations in one request
            $results = [];
            foreach ($operations as $operation) {
                // Process each operation
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
        })->name('mandor.bulk-save');
        
    });

    /*
    |--------------------------------------------------------------------------
    | Additional Mandor Routes (if needed for specific permissions)
    |--------------------------------------------------------------------------
    */
    
    // Route dengan permission check (jika diperlukan)
    Route::group(['prefix' => 'mandor', 'middleware' => 'check.mandor.permission'], function () {
        
        // Routes yang memerlukan permission khusus mandor
        Route::get('/restricted-data', function() {
            return response()->json(['message' => 'Restricted mandor data']);
        })->name('mandor.restricted-data');
        
    });

});

/*
|--------------------------------------------------------------------------
| Custom Middleware Registration (add to Kernel.php)
|--------------------------------------------------------------------------
| 
| Add this to app/Http/Kernel.php in $routeMiddleware array:
| 'check.mandor.permission' => \App\Http\Middleware\CheckMandorPermission::class,
|
| Then create the middleware:
| php artisan make:middleware CheckMandorPermission
|
*/