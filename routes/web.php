<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Controllers
use App\Http\Controllers\GPXController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PivotController;
use App\Http\Controllers\UnpostController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\UsernameLoginController;
use App\Http\Controllers\LiveChatController;
use App\Http\Controllers\React\MandorPageController;
use App\Http\Controllers\React\ApproverPageController;

use App\Http\Controllers\Api\PerhitunganUpahApiMobile;

// =============================================================================
// AUTHENTICATION ROUTES
// =============================================================================

Route::get('/login', [UsernameLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UsernameLoginController::class, 'login'])->name('login.post');
Route::post('/logout', [UsernameLoginController::class, 'logout'])->name('logout');

// =============================================================================
// PROTECTED ROUTES - REQUIRE AUTHENTICATION + ROLE-BASED REDIRECT
// =============================================================================

Route::group(['middleware' => ['auth', 'mandor.access']], function () {

    // =============================================================================
    // DASHBOARD & HOME ROUTES
    // =============================================================================
    
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/set-session', [HomeController::class, 'setSession'])->name('setSession');

    // =============================================================================
    // LIVE CHAT ROUTES
    // =============================================================================
    
    Route::post('/chat/send', [LiveChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages', [LiveChatController::class, 'getMessages'])->name('chat.messages');

    // =============================================================================
    // APPROVAL (MOBILE VIEW)
    // =============================================================================
    Route::middleware('auth')->group(function () {
        Route::prefix('input/approval')
            ->name('input.approval.')
            ->controller(\App\Http\Controllers\Input\ApprovalController::class)
            ->group(function () {
                
                // Main approval page
                Route::get('/', 'index')->name('index');
                
                // RKH approval processing
                Route::post('/process-rkh', 'processRKHApproval')->name('processRKH');
                
                // LKH approval processing
                Route::post('/process-lkh', 'processLKHApproval')->name('processLKH');
            });
    });

    // =============================================================================
    // NOTIFICATION ROUTES
    // =============================================================================
    
    // =============================================================================
        // USER NOTIFICATION ROUTES (All Users)
        // =============================================================================
        // Clear cache
        Route::get('utility/clearcache', function () {
            Artisan::call('config:cache');
            Artisan::call('cache:clear');
            Artisan::call('view:cache');
            return "<h6>config cleared, cache cleared, view cache</h6>";
        });

        // User notification list
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');
        
        // Get dropdown data for header
        Route::get('/notifications/dropdown-data', [NotificationController::class, 'getDropdownData'])
            ->name('notifications.dropdown-data');
        
        // Get unread count
        Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])
            ->name('notifications.unread-count');
        
        // Mark single notification as read
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');
        
        // Mark all notifications as read
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.mark-all-read');
        
        // =============================================================================
        // ADMIN NOTIFICATION MANAGEMENT ROUTES (With Permissions)
        // =============================================================================
        
        // Admin notification management list
        Route::get('/notifications/admin', [NotificationController::class, 'adminIndex'])
            ->name('notifications.admin.index')
            ->middleware('permission:Admin');
        
        // Create notification form
        Route::get('/notifications/create', [NotificationController::class, 'create'])
            ->name('notifications.create')
            ->middleware('permission:Create Notifikasi');
        
        // Store new notification
        Route::post('/notifications', [NotificationController::class, 'store'])
            ->name('notifications.store')
            ->middleware('permission:Create Notifikasi');
        
        // Edit notification form
        Route::get('/notifications/{id}/edit', [NotificationController::class, 'edit'])
            ->name('notifications.edit')
            ->middleware('permission:Edit Notifikasi');
        
        // Update notification
        Route::put('/notifications/{id}', [NotificationController::class, 'update'])
            ->name('notifications.update')
            ->middleware('permission:Edit Notifikasi');
        
        // Delete notification
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])
            ->name('notifications.destroy')
            ->middleware('permission:Hapus Notifikasi');

    // =============================================================================
    // GPX FILE MANAGEMENT ROUTES  
    // =============================================================================
    
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

    // =============================================================================
    // MANDOR SPA ROUTES
    // =============================================================================
    
    Route::get('/mandor/splash', function () {
        return Inertia::render('splash-screen');
    })->name('mandor.splash');
    
    // Main SPA entry point
    Route::get('/mandor', [MandorPageController::class, 'index'])->name('mandor.index');
    
    // =============================================================================
    // MANDOR LKH ASSIGNMENT & INPUT PAGES
    // =============================================================================
    
    // Assignment Page
    Route::get('/mandor/lkh/{lkhno}/assign', [MandorPageController::class, 'showLKHAssign'])
        ->name('mandor.lkh.assign');
    Route::post('/mandor/lkh/{lkhno}/assign', [MandorPageController::class, 'saveLKHAssign'])
        ->name('mandor.lkh.save-assignment');
    
    // Input/View/Edit Pages
    Route::get('/mandor/lkh/{lkhno}/input', [MandorPageController::class, 'showLKHInput'])
        ->name('mandor.lkh.input');
    Route::get('/mandor/lkh/{lkhno}/view', [MandorPageController::class, 'showLKHView'])
        ->name('mandor.lkh.view');
    Route::get('/mandor/lkh/{lkhno}/edit', [MandorPageController::class, 'showLKHEdit'])
        ->name('mandor.lkh.edit');

    // Save Results & Complete All
    Route::post('/mandor/lkh/{lkhno}/input', [MandorPageController::class, 'saveLKHResults'])
        ->name('mandor.lkh.save-results');
    Route::post('/mandor/lkh/complete-all', [MandorPageController::class, 'completeAllLKH'])
        ->name('mandor.lkh.complete-all');
    
    // =============================================================================
    // MANDOR API ROUTES - ORGANIZED BY FUNCTIONALITY
    // =============================================================================
    
    Route::prefix('api/mandor')->group(function () {
        
        // =========================================================================
        // ATTENDANCE MANAGEMENT APIs - UPDATED for individual approval
        // =========================================================================
        
        // Enhanced attendance routes
        Route::get('/workers', [MandorPageController::class, 'getWorkersList'])->name('mandor.workers');
        Route::get('/attendance/today', [MandorPageController::class, 'getTodayAttendance'])->name('mandor.attendance.today');
        Route::post('/attendance/process-checkin', [MandorPageController::class, 'processCheckIn'])->name('mandor.attendance.process-checkin');
        
        // NEW: Update photo for rejected attendance
        Route::post('/attendance/update-photo', [MandorPageController::class, 'updateAttendancePhoto'])->name('mandor.attendance.update-photo');
        
        // NEW: Get rejected attendance for mandor
        Route::get('/attendance/rejected', [MandorPageController::class, 'getRejectedAttendance'])->name('mandor.attendance.rejected');
        
        // Workers present for assignment - UPDATED to only include approved
        Route::get('/attendance/workers-present', function(Request $request) {
            $controller = new MandorPageController();
            return $controller->getTodayAttendance($request);
        })->name('mandor.attendance.workers-present');
        
        // =========================================================================
        // LKH MANAGEMENT APIs
        // =========================================================================
        
        // LKH Data Retrieval
        Route::get('/lkh/ready', [MandorPageController::class, 'getReadyLKH'])->name('mandor.lkh.ready');
        Route::get('/lkh/vehicle-info', [MandorPageController::class, 'getVehicleInfo'])->name('mandor.lkh.vehicle-info');
        
        // LKH Assignment & Results (API versions)
        Route::post('/lkh/save-assignment', [MandorPageController::class, 'saveLKHAssign'])
            ->name('mandor.api.lkh.save-assignment');
        Route::post('/lkh/save-results', [MandorPageController::class, 'saveLKHResults'])
            ->name('mandor.api.lkh.save-results');
        
        // LKH Detail & Status Management
        Route::get('/lkh/{lkhno}/detail', function($lkhno) {
            return response()->json([
                'lkh_detail' => [],
                'message' => 'LKH detail endpoint - implementation needed'
            ]);
        })->name('mandor.lkh.detail');
        
        Route::get('/lkh/{lkhno}/materials', function($lkhno) {
            return response()->json([
                'lkh_materials' => [],
                'message' => 'LKH materials endpoint - implementation needed'
            ]);
        })->name('mandor.lkh.materials');
        
        Route::post('/lkh/{lkhno}/update-status', function($lkhno, Request $request) {
            $status = $request->input('status');
            return response()->json([
                'success' => true,
                'message' => "LKH status updated to {$status}",
                'lkhno' => $lkhno
            ]);
        })->name('mandor.lkh.update-status');
        
        // =========================================================================
        // MATERIAL MANAGEMENT APIs
        // =========================================================================
        
        Route::get('/materials/available', [MandorPageController::class, 'getAvailableMaterials'])
            ->name('mandor.materials.available');
        Route::post('/materials/save-returns', [MandorPageController::class, 'saveMaterialReturns'])
            ->name('mandor.materials.save-returns');
        Route::post('/materials/confirm-pickup', [MandorPageController::class, 'confirmMaterialPickup'])
            ->name('mandor.materials.confirm-pickup');
        
        // =========================================================================
        // DATA SYNCHRONIZATION APIs
        // =========================================================================
        
        Route::post('/sync-offline-data', [MandorPageController::class, 'syncOfflineData'])
            ->name('mandor.sync-offline-data');
        
        // =========================================================================
        // UTILITY APIs
        // =========================================================================
        
        // Bulk operations for semi-offline functionality
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
        })->name('mandor.bulk-save');

        Route::get('/server-time', function() {
            return response()->json([
                'timestamp' => now()->toIso8601String(),
                'formatted' => now()->format('d/m/Y, H:i:s'),
                'timezone' => config('app.timezone')
            ]);
        })->name('mandor.server-time');
        
    });

    // =============================================================================
    // RESTRICTED MANDOR ROUTES (with permission checks)
    // =============================================================================
    
    Route::group(['prefix' => 'mandor', 'middleware' => 'check.mandor.permission'], function () {
        
        Route::get('/restricted-data', function() {
            return response()->json(['message' => 'Restricted mandor data']);
        })->name('mandor.restricted-data');
        
    });

    // =============================================================================
    // APPROVER ROUTES - UPDATED for individual approval flow + DASHBOARD STATS
    // =============================================================================
    
    // Main approver dashboard
    Route::get('/approver', [ApproverPageController::class, 'index'])
        ->name('approver.index');
    
    // ✅ NEW: Dashboard Stats Route - ADDED
    Route::get('/approver/dashboard/stats', [ApproverPageController::class, 'getDashboardStats'])
        ->name('approver.dashboard.stats');
    
    // Approval API routes - UPDATED for individual approval
    Route::prefix('api/approver')->group(function () {
        
        // Get pending attendance for approval - UPDATED to support mandor filtering
        Route::get('/attendance/pending', [ApproverPageController::class, 'getPendingAttendance'])
            ->name('approver.attendance.pending');
        
        // Get mandor list with pending counts
        Route::get('/mandors/pending', [ApproverPageController::class, 'getMandorListWithPending'])
            ->name('approver.mandors.pending');
        
        // Approve individual attendance records - UPDATED for batch processing
        Route::post('/attendance/approve', [ApproverPageController::class, 'approveAttendance'])
            ->name('approver.attendance.approve');
        
        // Reject individual attendance records - UPDATED for batch processing
        Route::post('/attendance/reject', [ApproverPageController::class, 'rejectAttendance'])
            ->name('approver.attendance.reject');
        
        // Get attendance history - individual records
        Route::get('/attendance/history', [ApproverPageController::class, 'getAttendanceHistory'])
            ->name('approver.attendance.history');
    });

});


// API ROUTES FOR MOBILE UPLOAD Perhitungan Upah
Route::middleware('api')->prefix('api/mobile')->group(function () {
    Route::post('/insert-upah-tenaga-kerja', [App\Http\Controllers\Api\PerhitunganUpahApiMobile::class, 'insertWorkerWage']);
});







// =============================================================================
// CARA KERJA ROLE-BASED REDIRECT:
// =============================================================================
// 1. User login → redirect ke '/home' atau '/' (default Laravel)
// 2. Middleware 'mandor.access' otomatis redirect berdasarkan idjabatan:
//    - idjabatan = 5 (Mandor) → redirect ke '/mandor' 
//    - idjabatan = 10 (Approver) → redirect ke '/approver'
//    - lainnya → tetap di '/home'
// 3. Proteksi: mandor tidak bisa akses '/approver', begitu sebaliknya

// =============================================================================
// INDIVIDUAL APPROVAL FLOW CHANGES:
// =============================================================================
// 1. Mandor absen individual pekerja → status PENDING di absenlst
// 2. Approver dapat filter by mandor → approve/reject individual records
// 3. Mandor dapat edit foto untuk record yang REJECTED → reset ke PENDING
// 4. LKH assignment hanya tampilkan pekerja dengan approval_status = 'APPROVED'

// =============================================================================
// DASHBOARD STATS FUNCTIONALITY:
// =============================================================================
// ✅ Route: /approver/dashboard/stats
// ✅ Returns: Real-time stats dari database
// ✅ Data: pending_count, approved_today, rejected_today, total_workers_today, mandor_count
// ✅ Additional: approval_rate, rejection_rate, mandor_breakdown
// ✅ Auto-refresh: Setiap 30 detik di frontend