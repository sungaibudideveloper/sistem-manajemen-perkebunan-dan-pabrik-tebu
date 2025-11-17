<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\React\MandorPageController;
use App\Http\Controllers\React\ApproverPageController;

/*
|--------------------------------------------------------------------------
| React SPA Routes - Inertia.js
|--------------------------------------------------------------------------
|
| Routes for React-based Single Page Applications using Inertia.js
| These routes serve both page rendering and AJAX data endpoints
| 
| Authentication: Session-based (web middleware)
| Access: Web browsers only
| Status: 
|   - Mandor: Legacy (Android team took over, kept for reference)
|   - Approver: Active (PWA still in use)
|
*/

Route::middleware(['auth', 'mandor.access'])->group(function () {

    // =============================================================================
    // MANDOR SPA - Main Pages (LEGACY - Android Took Over)
    // =============================================================================
    
    Route::get('/mandor/splash', function () {
        return Inertia::render('splash-screen');
    })->name('mandor.splash');
    
    Route::get('/mandor', [MandorPageController::class, 'index'])->name('mandor.index');
    
    // Mandor LKH Assignment & Input Pages
    Route::prefix('mandor/lkh')->name('mandor.lkh.')->group(function () {
        Route::get('/{lkhno}/assign', [MandorPageController::class, 'showLKHAssign'])->name('assign');
        Route::post('/{lkhno}/assign', [MandorPageController::class, 'saveLKHAssign'])->name('save-assignment');
        Route::get('/{lkhno}/input', [MandorPageController::class, 'showLKHInput'])->name('input');
        Route::get('/{lkhno}/view', [MandorPageController::class, 'showLKHView'])->name('view');
        Route::get('/{lkhno}/edit', [MandorPageController::class, 'showLKHEdit'])->name('edit');
        Route::post('/{lkhno}/input', [MandorPageController::class, 'saveLKHResults'])->name('save-results');
        Route::post('/complete-all', [MandorPageController::class, 'completeAllLKH'])->name('complete-all');
    });
    
    // =============================================================================
    // MANDOR SPA - Data Endpoints (AJAX/Fetch)
    // =============================================================================
    
    Route::prefix('mandor-data')->name('mandor.')->group(function () {
        
        // Attendance Management
        Route::get('/workers', [MandorPageController::class, 'getWorkersList'])
            ->name('workers');
        
        Route::get('/attendance/today', [MandorPageController::class, 'getTodayAttendance'])
            ->name('attendance.today');
        
        Route::post('/attendance/process-checkin', [MandorPageController::class, 'processCheckIn'])
            ->name('attendance.process-checkin');
        
        Route::post('/attendance/update-photo', [MandorPageController::class, 'updateAttendancePhoto'])
            ->name('attendance.update-photo');
        
        Route::get('/attendance/rejected', [MandorPageController::class, 'getRejectedAttendance'])
            ->name('attendance.rejected');
        
        Route::get('/attendance/workers-present', function(Request $request) {
            $controller = new MandorPageController();
            return $controller->getTodayAttendance($request);
        })->name('attendance.workers-present');
        
        // LKH Management
        Route::get('/lkh/ready', [MandorPageController::class, 'getReadyLKH'])
            ->name('lkh.ready');
        
        Route::get('/lkh/vehicle-info', [MandorPageController::class, 'getVehicleInfo'])
            ->name('lkh.vehicle-info');
        
        Route::post('/lkh/save-assignment', [MandorPageController::class, 'saveLKHAssign'])
            ->name('api.lkh.save-assignment');
        
        Route::post('/lkh/save-results', [MandorPageController::class, 'saveLKHResults'])
            ->name('api.lkh.save-results');
        
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
        
        // Material Management
        Route::get('/materials/available', [MandorPageController::class, 'getAvailableMaterials'])
            ->name('materials.available');
        
        Route::post('/materials/save-returns', [MandorPageController::class, 'saveMaterialReturns'])
            ->name('materials.save-returns');
        
        Route::post('/materials/confirm-pickup', [MandorPageController::class, 'confirmMaterialPickup'])
            ->name('materials.confirm-pickup');
        
        // Data Synchronization
        Route::post('/sync-offline-data', [MandorPageController::class, 'syncOfflineData'])
            ->name('sync-offline-data');
        
        // Utility Endpoints
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

    // Restricted Mandor Routes (with permission checks)
    Route::prefix('mandor')->middleware('check.mandor.permission')->group(function () {
        Route::get('/restricted-data', function() {
            return response()->json(['message' => 'Restricted mandor data']);
        })->name('mandor.restricted-data');
    });

    // =============================================================================
    // APPROVER SPA - Main Dashboard (ACTIVE)
    // =============================================================================
    
    Route::get('/approver', [ApproverPageController::class, 'index'])
        ->name('approver.index');
    
    // =============================================================================
    // APPROVER SPA - Data Endpoints (AJAX/Fetch)
    // =============================================================================
    
    Route::prefix('approver-data')->name('approver.')->group(function () {
        
        // Dashboard Stats
        Route::get('/dashboard/stats', [ApproverPageController::class, 'getDashboardStats'])
            ->name('dashboard.stats');
        
        // Attendance Approval Management
        Route::get('/pending-attendance', [ApproverPageController::class, 'getPendingAttendance'])
            ->name('attendance.pending');
        
        Route::get('/mandors-pending', [ApproverPageController::class, 'getMandorListWithPending'])
            ->name('mandors.pending');
        
        Route::post('/approve-attendance', [ApproverPageController::class, 'approveAttendance'])
            ->name('attendance.approve');
        
        Route::post('/reject-attendance', [ApproverPageController::class, 'rejectAttendance'])
            ->name('attendance.reject');
        
        Route::get('/attendance-history', [ApproverPageController::class, 'getAttendanceHistory'])
            ->name('attendance.history');
    });
});