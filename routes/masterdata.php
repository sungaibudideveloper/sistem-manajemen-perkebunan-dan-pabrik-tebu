<?php

use App\Http\Controllers\MasterData\ActivityController;
use App\Http\Controllers\MasterData\BlokController;
use App\Http\Controllers\MasterData\BatchController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\MasterListController;
use App\Http\Controllers\MasterData\PlottingController;
use App\Http\Controllers\MasterData\HerbisidaController;
use App\Http\Controllers\MasterData\HerbisidaGroupController;
use App\Http\Controllers\MasterData\HerbisidaDosageController;
use App\Http\Controllers\MasterData\ApprovalController;
use App\Http\Controllers\MasterData\KategoriController;
use App\Http\Controllers\MasterData\VarietasController;
use App\Http\Controllers\MasterData\AccountingController;
use App\Http\Controllers\MasterData\MandorController;
use App\Http\Controllers\MasterData\TenagaKerjaController;
use App\Http\Controllers\MasterData\Aplikasi\MenuController;
use App\Http\Controllers\MasterData\Aplikasi\SubmenuController;
use App\Http\Controllers\MasterData\Aplikasi\SubsubmenuController;
use App\Http\Controllers\MasterData\UpahController;
use App\Http\Controllers\MasterData\KendaraanController;
use App\Http\Controllers\MasterData\UserManagementController;
use App\Http\Controllers\MasterData\KontraktorController;
use App\Http\Controllers\MasterData\SubkontraktorController;

/*
|--------------------------------------------------------------------------
| Master Data Routes
|--------------------------------------------------------------------------
|
| Routes for managing master data throughout the application.
| Includes company, plot, worker, wage, and user management.
|
*/

// Company management
Route::middleware(['auth', 'permission:Company'])->group(function () {
    Route::get('masterdata/company', [CompanyController::class, 'index'])->name('masterdata.company.index');
    Route::post('masterdata/company', [CompanyController::class, 'handle'])->name('masterdata.company.handle');
});

Route::middleware('permission:Edit Company')->group(function () {
    Route::put('masterdata/company/{companycode}', [CompanyController::class, 'update'])->name('masterdata.company.update');
});

Route::middleware('permission:Hapus Company')->group(function () {
    Route::delete('masterdata/company/{companycode}', [CompanyController::class, 'destroy'])->name('masterdata.company.destroy');
});

// Blok management
Route::middleware(['auth', 'permission:Blok'])->group(function () {
    Route::get('masterdata/blok', [BlokController::class, 'index'])->name('masterdata.blok.index');
    Route::post('masterdata/blok', [BlokController::class, 'handle'])->name('masterdata.blok.handle');
});

Route::middleware('permission:Edit Blok')->group(function () {
    Route::put('masterdata/blok/{blok}/{companycode}', [BlokController::class, 'update'])->name('masterdata.blok.update');
});

Route::middleware('permission:Hapus Blok')->group(function () {
    Route::delete('masterdata/blok/{blok}/{companycode}', [BlokController::class, 'destroy'])->name('masterdata.blok.destroy');
});

// Plotting management
Route::middleware(['auth', 'permission:Plotting'])->group(function () {
    Route::get('masterdata/plotting', [PlottingController::class, 'index'])->name('masterdata.plotting.index');
    Route::post('masterdata/plotting', [PlottingController::class, 'handle'])->name('masterdata.plotting.handle');
    Route::post('masterdata/plotting/add-to-masterlist', [PlottingController::class, 'addToMasterlist'])->name('masterdata.plotting.addToMasterlist');
});

Route::middleware('permission:Edit Plotting')->group(function () {
    Route::put('masterdata/plotting/{plot}/{companycode}', [PlottingController::class, 'update'])->name('masterdata.plotting.update');
});

Route::middleware('permission:Hapus Plotting')->group(function () {
    Route::delete('masterdata/plotting/{plot}/{companycode}', [PlottingController::class, 'destroy'])->name('masterdata.plotting.destroy');
});

// Herbisida management
Route::middleware(['auth', 'permission:Herbisida'])->group(function () {
    Route::get('masterdata/herbisida', [HerbisidaController::class, 'index'])->name('masterdata.herbisida.index');
    Route::post('masterdata/herbisida', [HerbisidaController::class, 'store'])->name('masterdata.herbisida.store');
    Route::get('masterdata/herbisida/group', [HerbisidaController::class, 'group'])->name('masterdata.herbisida.group');
    Route::get('masterdata/herbisida/items', function (\Illuminate\Http\Request $request) {
        return \App\Models\Herbisida::where('companycode', $request->companycode)
            ->select('itemcode', 'itemname')
            ->orderBy('itemcode')
            ->get();
    })->name('masterdata.herbisida.items');
});

Route::middleware(['auth', 'permission:Edit Herbisida'])->group(function () {
    Route::match(['put', 'patch'], 'masterdata/herbisida/{companycode}/{itemcode}', [HerbisidaController::class, 'update'])->name('masterdata.herbisida.update');
});

Route::middleware(['auth', 'permission:Hapus Herbisida'])->group(function () {
    Route::delete('masterdata/herbisida/{companycode}/{itemcode}', [HerbisidaController::class, 'destroy'])->name('masterdata.herbisida.destroy');
});

// Herbisida group management
Route::get('masterdata/herbisida-group', [HerbisidaGroupController::class, 'home'])->name('masterdata.herbisida-group.index');
Route::post('masterdata/herbisida-group', [HerbisidaGroupController::class, 'insert']);
Route::patch('masterdata/herbisida-group/{id}', [HerbisidaGroupController::class, 'edit']);
Route::delete('masterdata/herbisida-group/{id}', [HerbisidaGroupController::class, 'delete']);

// Herbisida dosage management
Route::middleware(['auth', 'permission:Dosis Herbisida'])->group(function () {
    Route::get('masterdata/herbisida-dosage', [HerbisidaDosageController::class, 'index'])->name('masterdata.herbisida-dosage.index');
    Route::post('masterdata/herbisida-dosage', [HerbisidaDosageController::class, 'store'])->name('masterdata.herbisida-dosage.store');
});

Route::middleware(['auth', 'permission:Edit Dosis Herbisida'])->group(function () {
    Route::match(['put', 'patch'], 'masterdata/herbisida-dosage/{companycode}/{activitycode}/{itemcode}', [HerbisidaDosageController::class, 'update'])->name('masterdata.herbisida-dosage.update');
});

Route::middleware(['auth', 'permission:Hapus Dosis Herbisida'])->group(function () {
    Route::delete('masterdata/herbisida-dosage/{companycode}/{activitycode}/{itemcode}', [HerbisidaDosageController::class, 'destroy'])->name('masterdata.herbisida-dosage.destroy');
});

Route::resource('herbisida-dosage', HerbisidaDosageController::class);

// Activity management
Route::get('masterdata/aktivitas', [ActivityController::class, 'index'])->name('masterdata.aktivitas.index');
Route::post('masterdata/aktivitas', [ActivityController::class, 'store'])->name('masterdata.aktivitas.store');
Route::put('masterdata/aktivitas/{aktivitas}', [ActivityController::class, 'update'])->name('masterdata.aktivitas.update');
Route::delete('masterdata/aktivitas/{aktivitas}', [ActivityController::class, 'destroy'])->name('masterdata.aktivitas.destroy');

// Approval management
Route::middleware(['auth', 'permission:Approval'])->group(function () {
    Route::get('masterdata/approval', [ApprovalController::class, 'index'])->name('masterdata.approval.index');
    Route::post('masterdata/approval', [ApprovalController::class, 'store'])->name('masterdata.approval.store');
});

Route::middleware(['auth', 'permission:Edit Approval'])->group(function () {
    Route::match(['put', 'patch'], 'masterdata/approval/{companycode}/{category}', [ApprovalController::class, 'update'])->name('masterdata.approval.update');
});

Route::middleware(['auth', 'permission:Hapus Approval'])->group(function () {
    Route::delete('masterdata/approval/{companycode}/{category}', [ApprovalController::class, 'destroy'])->name('masterdata.approval.destroy');
});

// Kategori management
Route::middleware(['auth', 'permission:Kategori'])->group(function () {
    Route::get('masterdata/kategori', [KategoriController::class, 'index'])->name('masterdata.kategori.index');
    Route::post('masterdata/kategori', [KategoriController::class, 'store'])->name('masterdata.kategori.store');
});

Route::middleware(['auth', 'permission:Edit Kategori'])->group(function () {
    Route::match(['put', 'patch'], 'masterdata/kategori/{kodekategori}', [KategoriController::class, 'update'])->name('masterdata.kategori.update');
});

Route::middleware(['auth', 'permission:Hapus Kategori'])->group(function () {
    Route::delete('masterdata/kategori/{kodekategori}', [KategoriController::class, 'destroy'])->name('masterdata.kategori.destroy');
});

// Varietas management
Route::middleware(['auth', 'permission:Varietas'])->group(function () {
    Route::get('masterdata/varietas', [VarietasController::class, 'index'])->name('masterdata.varietas.index');
    Route::post('masterdata/varietas', [VarietasController::class, 'store'])->name('masterdata.varietas.store');
});

Route::middleware(['auth', 'permission:Edit Varietas'])->group(function () {
    Route::match(['put', 'patch'], 'masterdata/varietas/{kodevarietas}', [VarietasController::class, 'update'])->name('masterdata.varietas.update');
});

Route::middleware(['auth', 'permission:Hapus Varietas'])->group(function () {
    Route::delete('masterdata/varietas/{kodevarietas}', [VarietasController::class, 'destroy'])->name('masterdata.varietas.destroy');
});

// Accounting management
Route::middleware(['auth', 'permission:Accounting'])->group(function () {
    Route::get('masterdata/accounting', [AccountingController::class, 'index'])->name('masterdata.accounting.index');
    Route::post('masterdata/accounting', [AccountingController::class, 'store'])->name('masterdata.accounting.store');
});

Route::middleware(['auth', 'permission:Edit Accounting'])->group(function () {
    Route::match(['put', 'patch'], 'masterdata/accounting/{activitycode}/{jurnalaccno}/{jurnalacctype}', [AccountingController::class, 'update'])->name('masterdata.accounting.update');
});

Route::middleware(['auth', 'permission:Hapus Accounting'])->group(function () {
    Route::delete('masterdata/accounting/{activitycode}/{jurnalaccno}/{jurnalacctype}', [AccountingController::class, 'destroy'])->name('masterdata.accounting.destroy');
});

// Master list management
Route::middleware(['auth', 'permission:MasterList'])->group(function () {
    Route::get('masterdata/master-list', [MasterListController::class, 'index'])->name('masterdata.master-list.index');
    Route::post('masterdata/master-list', [MasterListController::class, 'store'])->name('masterdata.master-list.store');
});

Route::middleware(['auth', 'permission:Edit MasterList'])->group(function () {
    Route::match(['put', 'patch'], 'masterdata/master-list/{companycode}/{plot}', [MasterListController::class, 'update'])->name('masterdata.master-list.update');
});

Route::middleware(['auth', 'permission:Hapus MasterList'])->group(function () {
    Route::delete('masterdata/master-list/{companycode}/{plot}', [MasterListController::class, 'destroy'])->name('masterdata.master-list.destroy');
});

// Batch management
Route::middleware(['auth', 'permission:Batch'])->group(function () {
    Route::get('masterdata/batch', [BatchController::class, 'index'])->name('masterdata.batch.index');
    Route::post('masterdata/batch', [BatchController::class, 'store'])->name('masterdata.batch.store');
});

Route::middleware(['auth', 'permission:Edit Batch'])->group(function () {
    Route::match(['put', 'patch'], 'masterdata/batch/{batchno}', [BatchController::class, 'update'])->name('masterdata.batch.update');
});

Route::middleware(['auth', 'permission:Hapus Batch'])->group(function () {
    Route::delete('masterdata/batch/{batchno}', [BatchController::class, 'destroy'])->name('masterdata.batch.destroy');
});

// Mandor management
Route::get('masterdata/mandor', [MandorController::class, 'index'])->name('masterdata.mandor.index');
Route::post('masterdata/mandor', [MandorController::class, 'store'])->name('masterdata.mandor.store');
Route::match(['put', 'patch'], 'masterdata/mandor/{companycode}/{id}', [MandorController::class, 'update'])->name('masterdata.mandor.update');
Route::delete('masterdata/mandor/{companycode}/{id}', [MandorController::class, 'destroy'])->name('masterdata.mandor.destroy');

// Tenaga kerja management
Route::get('masterdata/tenagakerja', [TenagaKerjaController::class, 'index'])->name('masterdata.tenagakerja.index');
Route::post('masterdata/tenagakerja', [TenagaKerjaController::class, 'store'])->name('masterdata.tenagakerja.store');
Route::match(['put', 'patch'], 'masterdata/tenagakerja/{companycode}/{id}', [TenagaKerjaController::class, 'update'])->name('masterdata.tenagakerja.update');
Route::delete('masterdata/tenagakerja/{companycode}/{id}', [TenagaKerjaController::class, 'destroy'])->name('masterdata.tenagakerja.destroy');

// Menu management
Route::middleware(['auth', 'permission:Menu'])->group(function () {
    Route::get('usermanagement/menu', [MenuController::class, 'index'])->name('usermanagement.menu.index');
    Route::post('usermanagement/menu', [MenuController::class, 'store'])->name('usermanagement.menu.store');
});

Route::middleware(['auth', 'permission:Edit Menu'])->group(function () {
    Route::put('usermanagement/menu/{menuid}', [MenuController::class, 'update'])->name('usermanagement.menu.update');
});

Route::middleware(['auth', 'permission:Hapus Menu'])->group(function () {
    Route::delete('usermanagement/menu/{menuid}/{name}', [MenuController::class, 'destroy'])->name('usermanagement.menu.destroy');
});

// Submenu management
Route::middleware(['auth', 'permission:Submenu'])->group(function () {
    Route::get('usermanagement/submenu', [SubmenuController::class, 'index'])->name('usermanagement.submenu.index');
    Route::post('usermanagement/submenu', [SubmenuController::class, 'store'])->name('usermanagement.submenu.store');
});

Route::middleware(['auth', 'permission:Edit Submenu'])->group(function () {
    Route::put('usermanagement/submenu/{submenuid}', [SubmenuController::class, 'update'])->name('usermanagement.submenu.update');
});

Route::middleware(['auth', 'permission:Hapus Submenu'])->group(function () {
    Route::delete('usermanagement/submenu/{submenuid}/{name}', [SubmenuController::class, 'destroy'])->name('usermanagement.submenu.destroy');
});

// Subsubmenu management
Route::middleware(['auth', 'permission:Subsubmenu'])->group(function () {
    Route::get('usermanagement/subsubmenu', [SubsubmenuController::class, 'index'])->name('usermanagement.subsubmenu.index');
    Route::post('usermanagement/subsubmenu', [SubsubmenuController::class, 'store'])->name('usermanagement.subsubmenu.store');
});

Route::middleware(['auth', 'permission:Edit Subsubmenu'])->group(function () {
    Route::put('usermanagement/subsubmenu/{subsubmenuid}', [SubsubmenuController::class, 'update'])->name('usermanagement.subsubmenu.update');
});

Route::middleware(['auth', 'permission:Hapus Subsubmenu'])->group(function () {
    Route::delete('usermanagement/subsubmenu/{subsubmenuid}/{name}', [SubsubmenuController::class, 'destroy'])->name('usermanagement.subsubmenu.destroy');
});

// Upah management
Route::middleware(['auth', 'permission:Upah'])->group(function () {
    Route::get('masterdata/upah', [UpahController::class, 'index'])->name('masterdata.upah.index');
    Route::post('masterdata/upah', [UpahController::class, 'store'])->name('masterdata.upah.store');
});

Route::middleware(['auth', 'permission:Edit Upah'])->group(function () {
    Route::put('masterdata/upah/{id}', [UpahController::class, 'update'])->name('masterdata.upah.update');
});

Route::middleware(['auth', 'permission:Hapus Upah'])->group(function () {
    Route::delete('masterdata/upah/{id}', [UpahController::class, 'destroy'])->name('masterdata.upah.destroy');
});

// Kendaraan management
Route::middleware(['auth', 'permission:Kendaraan'])->group(function () {
    Route::get('masterdata/kendaraan', [KendaraanController::class, 'index'])->name('masterdata.kendaraan.index');
    Route::post('masterdata/kendaraan', [KendaraanController::class, 'handle'])->name('masterdata.kendaraan.handle');
});

Route::middleware(['auth', 'permission:Edit Kendaraan'])->group(function () {
    Route::put('masterdata/kendaraan/{companycode}/{nokendaraan}', [KendaraanController::class, 'update'])->name('masterdata.kendaraan.update');
});

Route::middleware(['auth', 'permission:Hapus Kendaraan'])->group(function () {
    Route::delete('masterdata/kendaraan/{companycode}/{nokendaraan}', [KendaraanController::class, 'destroy'])->name('masterdata.kendaraan.destroy');
});

// Kontraktor management
Route::middleware(['auth', 'permission:Kontraktor'])->group(function () {
    Route::get('masterdata/kontraktor', [KontraktorController::class, 'index'])->name('masterdata.kontraktor.index');
    Route::post('masterdata/kontraktor', [KontraktorController::class, 'store'])->name('masterdata.kontraktor.store');
});

Route::middleware(['auth', 'permission:Edit Kontraktor'])->group(function () {
    Route::match(['put', 'patch'], 'masterdata/kontraktor/{companycode}/{id}', [KontraktorController::class, 'update'])->name('masterdata.kontraktor.update');
});

Route::middleware(['auth', 'permission:Hapus Kontraktor'])->group(function () {
    Route::delete('masterdata/kontraktor/{companycode}/{id}', [KontraktorController::class, 'destroy'])->name('masterdata.kontraktor.destroy');
});

// Subkontraktor management
Route::middleware(['auth', 'permission:Subkontraktor'])->group(function () {
    Route::get('masterdata/subkontraktor', [SubkontraktorController::class, 'index'])->name('masterdata.subkontraktor.index');
    Route::post('masterdata/subkontraktor', [SubkontraktorController::class, 'store'])->name('masterdata.subkontraktor.store');
});

Route::middleware(['auth', 'permission:Edit Subkontraktor'])->group(function () {
    Route::match(['put', 'patch'], 'masterdata/subkontraktor/{companycode}/{id}', [SubkontraktorController::class, 'update'])->name('masterdata.subkontraktor.update');
});

Route::middleware(['auth', 'permission:Hapus Subkontraktor'])->group(function () {
    Route::delete('masterdata/subkontraktor/{companycode}/{id}', [SubkontraktorController::class, 'destroy'])->name('masterdata.subkontraktor.destroy');
});

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