<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InfoUpdates\NotificationController;

Route::middleware('auth')->prefix('info-updates')->name('info-updates.')->group(function () {
    
    // ============================================================================
    // NOTIFICATIONS (User View)
    // ============================================================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/dropdown-data', [NotificationController::class, 'getDropdownData'])->name('dropdown-data');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    // ============================================================================
    // NOTIFICATIONS (Admin Management)
    // ============================================================================
    Route::prefix('notifications/admin')->name('notifications.admin.')->group(function () {
        Route::middleware('permission:infoupdates.notification.view')->group(function () {
            Route::get('/', [NotificationController::class, 'adminIndex'])->name('index');
        });

        Route::middleware('permission:infoupdates.notification.create')->group(function () {
            Route::get('/create', [NotificationController::class, 'create'])->name('create');
            Route::post('/', [NotificationController::class, 'store'])->name('store');
        });

        Route::middleware('permission:infoupdates.notification.edit')->group(function () {
            Route::get('/{id}/edit', [NotificationController::class, 'edit'])->name('edit');
            Route::put('/{id}', [NotificationController::class, 'update'])->name('update');
        });

        Route::middleware('permission:infoupdates.notification.delete')->group(function () {
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        });
    });
});