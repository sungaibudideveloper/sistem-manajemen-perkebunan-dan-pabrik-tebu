<?php

// routes/user-management.php

use App\Http\Controllers\UserManagement\{
    UserController,
    JabatanController,
    PermissionController,
    UserCompanyController,
    UserPermissionController,
    UserActivityController,
    SupportTicketController
};

// ============================================================================
// USER MANAGEMENT ROUTES
// ============================================================================

Route::prefix('usermanagement')->middleware('auth')->name('usermanagement.')->group(function () {

    

    // User AJAX Endpoints
    Route::prefix('ajax/users')->name('ajax.user.')->group(function () {
        Route::get('{userid}/permissions', [UserController::class, 'getPermissions'])->name('permissions');
        Route::get('{userid}/companies', [UserController::class, 'getCompanies'])->name('companies');
        Route::get('{userid}/activities', [UserController::class, 'getActivities'])->name('activities');
    });
    
    // ------------------------------------------------------------------------
    // USER MANAGEMENT
    // ------------------------------------------------------------------------
    Route::middleware('permission:usermanagement.user.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('user.index');
        Route::get('users/{userid}', [UserController::class, 'show'])->name('user.show');
    });

    Route::middleware('permission:usermanagement.user.create')->group(function () {
        Route::post('users', [UserController::class, 'store'])->name('user.store');
    });

    Route::middleware('permission:usermanagement.user.edit')->group(function () {
        Route::put('users/{userid}', [UserController::class, 'update'])->name('user.update');
    });

    Route::middleware('permission:usermanagement.user.delete')->group(function () {
        Route::delete('users/{userid}', [UserController::class, 'destroy'])->name('user.destroy');
    });

    // ------------------------------------------------------------------------
    // JABATAN (ROLE) MANAGEMENT
    // ------------------------------------------------------------------------
    Route::middleware('permission:usermanagement.jabatan.view')->group(function () {
        Route::get('jabatan', [JabatanController::class, 'index'])->name('jabatan.index');
    });

    Route::middleware('permission:usermanagement.jabatan.create')->group(function () {
        Route::post('jabatan', [JabatanController::class, 'store'])->name('jabatan.store');
    });

    Route::middleware('permission:usermanagement.jabatan.edit')->group(function () {
        Route::put('jabatan/{idjabatan}', [JabatanController::class, 'update'])->name('jabatan.update');
    });

    Route::middleware('permission:usermanagement.jabatan.delete')->group(function () {
        Route::delete('jabatan/{idjabatan}', [JabatanController::class, 'destroy'])->name('jabatan.destroy');
    });

    Route::middleware('permission:usermanagement.jabatan.assign-permission')->group(function () {
        Route::post('jabatan/assign-permissions', [JabatanController::class, 'assignPermissions'])
            ->name('jabatan.assign-permissions');
        // AJAX Endpoints
        Route::get('ajax/jabatan/{idjabatan}/permissions', [JabatanController::class, 'getPermissions'])
            ->name('ajax.jabatan.permissions');
    });

    // ------------------------------------------------------------------------
    // PERMISSION MASTER DATA
    // ------------------------------------------------------------------------
    Route::middleware('permission:usermanagement.permission.view')->group(function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permission.index');
    });

    Route::middleware('permission:usermanagement.permission.create')->group(function () {
        Route::post('permissions', [PermissionController::class, 'store'])->name('permission.store');
    });

    Route::middleware('permission:usermanagement.permission.edit')->group(function () {
        Route::put('permissions/{id}', [PermissionController::class, 'update'])->name('permission.update');
    });

    Route::middleware('permission:usermanagement.permission.delete')->group(function () {
        Route::delete('permissions/{id}', [PermissionController::class, 'destroy'])->name('permission.destroy');
    });

    // ------------------------------------------------------------------------
    // USER COMPANY ACCESS
    // ------------------------------------------------------------------------
    Route::middleware('permission:usermanagement.user-company.view')->group(function () {
        Route::get('user-companies', [UserCompanyController::class, 'index'])->name('user-company.index');
    });

    Route::middleware('permission:usermanagement.user-company.assign')->group(function () {
        Route::post('user-companies', [UserCompanyController::class, 'store'])->name('user-company.store');
        Route::post('user-companies/assign', [UserCompanyController::class, 'assign'])->name('user-company.assign');
    });

    Route::middleware('permission:usermanagement.user-company.delete')->group(function () {
        Route::delete('user-companies/{userid}/{companycode}', [UserCompanyController::class, 'destroy'])->name('user-company.destroy');
    });

    // ------------------------------------------------------------------------
    // USER PERMISSION OVERRIDES
    // ------------------------------------------------------------------------
    Route::middleware('permission:usermanagement.user-permission.view')->group(function () {
        Route::get('user-permissions', [UserPermissionController::class, 'index'])->name('user-permission.index');
    });

    Route::middleware('permission:usermanagement.user-permission.create')->group(function () {
        Route::post('user-permissions', [UserPermissionController::class, 'store'])->name('user-permission.store');
    });

    Route::middleware('permission:usermanagement.user-permission.delete')->group(function () {
        Route::delete('user-permissions/{id}', [UserPermissionController::class, 'destroy'])->name('user-permission.destroy');
    });

    // ------------------------------------------------------------------------
    // USER ACTIVITY PERMISSIONS
    // ------------------------------------------------------------------------
    Route::middleware('permission:usermanagement.user-activity.view')->group(function () {
        Route::get('user-activities', [UserActivityController::class, 'index'])->name('user-activity.index');
        Route::get('user-activities/{userid}/{companycode}', [UserActivityController::class, 'show'])->name('user-activity.show');
    });

    Route::middleware('permission:usermanagement.user-activity.assign')->group(function () {
        Route::post('user-activities/assign', [UserActivityController::class, 'assign'])->name('user-activity.assign');
    });

    Route::middleware('permission:usermanagement.user-activity.delete')->group(function () {
        Route::delete('user-activities/{userid}/{companycode}', [UserActivityController::class, 'destroy'])->name('user-activity.destroy');
    });

    // ------------------------------------------------------------------------
    // SUPPORT TICKET
    // ------------------------------------------------------------------------
    Route::middleware('permission:usermanagement.support-ticket.view')->group(function () {
        Route::get('support-tickets', [SupportTicketController::class, 'index'])->name('support-ticket.index');
        Route::get('support-tickets/{id}', [SupportTicketController::class, 'show'])->name('support-ticket.show');
    });

    Route::middleware('permission:usermanagement.support-ticket.edit')->group(function () {
        Route::put('support-tickets/{id}', [SupportTicketController::class, 'update'])->name('support-ticket.update');
    });

    Route::middleware('permission:usermanagement.support-ticket.delete')->group(function () {
        Route::delete('support-tickets/{id}', [SupportTicketController::class, 'destroy'])->name('support-ticket.destroy');
    });
});

// Public Support Ticket Submission (No auth required)
Route::post('support-ticket/submit', [SupportTicketController::class, 'publicStore'])
    ->middleware('throttle:10,60')
    ->name('support.ticket.submit');