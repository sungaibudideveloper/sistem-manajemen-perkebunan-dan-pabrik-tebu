<?php

// routes\user-management.php
use App\Http\Controllers\MasterData\UserManagementController;



// User management
Route::middleware(['auth', 'permission:Kelola User'])->group(function () {
    Route::get('usermanagement/user', [UserManagementController::class, 'userIndex'])->name('usermanagement.user.index');
    Route::post('usermanagement/user', [UserManagementController::class, 'userStore'])->name('usermanagement.user.store');
    Route::get('usermanagement/user/create', [UserManagementController::class, 'userCreate'])->name('usermanagement.user.create');

    Route::get('usermanagement/user-company-permissions', [UserManagementController::class, 'userCompanyIndex'])->name('usermanagement.user-company-permissions.index');
    Route::post('usermanagement/user-company-permissions', [UserManagementController::class, 'userCompanyStore'])->name('usermanagement.user-company-permissions.store');
    Route::post('usermanagement/user-company-permissions/assign', [UserManagementController::class, 'userCompanyAssign'])->name('usermanagement.user-company-permissions.assign');

    Route::get('usermanagement/user-permissions', [UserManagementController::class, 'userPermissionIndex'])->name('usermanagement.user-permissions.index');
    Route::post('usermanagement/user-permissions', [UserManagementController::class, 'userPermissionStore'])->name('usermanagement.user-permissions.store');
});

Route::middleware(['auth', 'permission:Edit User'])->group(function () {
    Route::get('usermanagement/user/{userid}/edit', [UserManagementController::class, 'userEdit'])->name('usermanagement.user.edit');
    Route::put('usermanagement/user/{userid}', [UserManagementController::class, 'userUpdate'])->name('usermanagement.user.update');
    Route::delete('usermanagement/user-company-permissions/{userid}/{companycode}', [UserManagementController::class, 'userCompanyDestroy'])->name('usermanagement.user-company-permissions.destroy');
    Route::delete('usermanagement/user-permissions/{userid}/{companycode}/{permission}', [UserManagementController::class, 'userPermissionDestroy'])->name('usermanagement.user-permissions.destroy');
});

Route::middleware('permission:Hapus User')->group(function () {
    Route::delete('usermanagement/user/{userid}', [UserManagementController::class, 'userDestroy'])->name('usermanagement.user.destroy');
});

// Permission master data management
Route::middleware(['auth', 'permission:Master'])->group(function () {
    Route::get('usermanagement/permissions-masterdata', [UserManagementController::class, 'permissionIndex'])->name('usermanagement.permissions-masterdata.index');
    Route::post('usermanagement/permissions-masterdata', [UserManagementController::class, 'permissionStore'])->name('usermanagement.permissions-masterdata.store');
    Route::put('usermanagement/permissions-masterdata/{permissionid}', [UserManagementController::class, 'permissionUpdate'])->name('usermanagement.permissions-masterdata.update');
    Route::delete('usermanagement/permissions-masterdata/{permissionid}', [UserManagementController::class, 'permissionDestroy'])->name('usermanagement.permissions-masterdata.destroy');

    Route::get('usermanagement/test-permission/{userid}/{permission}', [UserManagementController::class, 'testUserPermission'])->name('usermanagement.test-permission');
});

// Jabatan permission management
Route::middleware(['auth', 'permission:Jabatan'])->group(function () {
    Route::get('usermanagement/jabatan', [UserManagementController::class, 'jabatanPermissionIndex'])->name('usermanagement.jabatan.index');
    Route::post('usermanagement/jabatan/assign-permission', [UserManagementController::class, 'jabatanPermissionStore'])->name('usermanagement.jabatan.assign-permission');
    Route::delete('usermanagement/jabatan/remove-permission', [UserManagementController::class, 'jabatanPermissionDestroy'])->name('usermanagement.jabatan.remove-permission');
    Route::post('usermanagement/jabatan', [UserManagementController::class, 'jabatanStore'])->name('usermanagement.jabatan.store');
    Route::put('usermanagement/jabatan/{idjabatan}', [UserManagementController::class, 'jabatanUpdate'])->name('usermanagement.jabatan.update');
    Route::delete('usermanagement/jabatan/{idjabatan}', [UserManagementController::class, 'jabatanDestroy'])->name('usermanagement.jabatan.destroy');
});

// Support ticket management
Route::middleware(['auth', 'permission:Kelola User'])->group(function () {
    Route::get('usermanagement/support-ticket', [UserManagementController::class, 'ticketIndex'])->name('usermanagement.support-ticket.index');
    Route::put('usermanagement/support-ticket/{ticket_id}', [UserManagementController::class, 'ticketUpdate'])->name('usermanagement.support-ticket.update');
    Route::delete('usermanagement/support-ticket/{ticket_id}', [UserManagementController::class, 'ticketDestroy'])->name('usermanagement.support-ticket.destroy');

    Route::get('usermanagement/user-activity-permission', [UserManagementController::class, 'UserActivityPermission'])->name('usermanagement.user-activity-permission.index');
    Route::post('usermanagement/user-activity-permission/assign', [UserManagementController::class, 'userActivityAssign'])->name('usermanagement.user-activity-permission.assign');
    Route::delete('usermanagement/user-activity-permission/{userid}/{companycode}/{activitygroup}', [UserManagementController::class, 'userActivityDestroy'])->name('usermanagement.user-activity-permission.destroy');
    Route::get('usermanagement/user-activity-permission/{userid}/{companycode?}', [UserManagementController::class, 'getUserActivitiesForCurrentCompany']);
});

Route::post('support-ticket/submit', [UserManagementController::class, 'ticketStore'])
    ->middleware('throttle:10,60')
    ->name('support.ticket.submit');


// User Management AJAX endpoints
Route::middleware('auth')->prefix('usermanagement/ajax')->name('usermanagement.ajax.')->group(function () {

    // Get jabatan permissions for modal/form dropdown
    Route::get('/jabatan/{idjabatan}/permissions', [UserManagementController::class, 'getJabatanPermissions'])
        ->name('jabatan-permissions');

    // Get user permissions for display in modal
    Route::get('/user/{userid}/permissions', [UserManagementController::class, 'getUserPermissionsSimple'])
        ->name('user-permissions');
});