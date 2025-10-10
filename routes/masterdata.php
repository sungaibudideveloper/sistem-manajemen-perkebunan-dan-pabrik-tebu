<?php

use App\Http\Controllers\MasterData\ActivityController;
use App\Http\Controllers\MasterData\BlokController;
use App\Http\Controllers\MasterData\BatchController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\MasterListController;
use App\Http\Controllers\MasterData\PlottingController;
use App\Http\Controllers\MasterData\UsernameController;
use App\Http\Controllers\MasterData\HerbisidaController;
use App\Http\Controllers\MasterData\HerbisidaDosageController;
use App\Http\Controllers\MasterData\JabatanController;
use App\Http\Controllers\MasterData\ApprovalController;
use App\Http\Controllers\MasterData\KategoriController;
use App\Http\Controllers\MasterData\StatusController;
use App\Http\Controllers\MasterData\VarietasController;
use App\Http\Controllers\MasterData\AccountingController;
use App\Http\Controllers\MasterData\MandorController;
use App\Http\Controllers\MasterData\TenagaKerjaController;
use App\Http\Controllers\MasterData\AplikasiController;
use App\Http\Controllers\MasterData\Aplikasi\MenuController;
use App\Http\Controllers\MasterData\Aplikasi\SubmenuController;
use App\Http\Controllers\MasterData\Aplikasi\SubsubmenuController;
use App\Http\Controllers\MasterData\UpahController;
use App\Http\Controllers\MasterData\KendaraanController;
use App\Http\Controllers\MasterData\UserManagementController;



//company
Route::group(['middleware' => ['auth', 'permission:Company']], function () {
    Route::post('masterdata/company', [CompanyController::class, 'handle'])->name('masterdata.company.handle');
    Route::get('masterdata/company', [CompanyController::class, 'index'])->name('masterdata.company.index');
});
Route::put('masterdata/company/{companycode}', [CompanyController::class, 'update'])->name('masterdata.company.update')->middleware('permission:Edit Company');
Route::delete('masterdata/company/{companycode}', [CompanyController::class, 'destroy'])
    ->name('masterdata.company.destroy')->middleware('permission:Hapus Company');

//Blok
Route::group(['middleware' => ['auth', 'permission:Blok']], function () {
    Route::get('masterdata/blok', [BlokController::class, 'index'])->name('masterdata.blok.index');
    Route::post('masterdata/blok', [BlokController::class, 'handle'])->name('masterdata.blok.handle');
});
Route::delete('masterdata/blok/{blok}/{companycode}', [BlokController::class, 'destroy'])
    ->name('masterdata.blok.destroy')->middleware('permission:Hapus Blok');
Route::put('masterdata/blok/{blok}/{companycode}', [BlokController::class, 'update'])
    ->name('masterdata.blok.update')->middleware('permission:Edit Blok');

//Plotting
Route::group(['middleware' => ['auth', 'permission:Plotting']], function () {
    Route::post('masterdata/plotting', [PlottingController::class, 'handle'])->name('masterdata.plotting.handle');
    Route::get('masterdata/plotting', [PlottingController::class, 'index'])->name('masterdata.plotting.index');
    Route::post('masterdata/plotting/add-to-masterlist', [PlottingController::class, 'addToMasterlist'])->name('masterdata.plotting.addToMasterlist');
});
Route::delete('masterdata/plotting/{plot}/{companycode}', [PlottingController::class, 'destroy'])
    ->name('masterdata.plotting.destroy')->middleware('permission:Hapus Plotting');
Route::put('masterdata/plotting/{plot}/{companycode}', [PlottingController::class, 'update'])
    ->name('masterdata.plotting.update')->middleware('permission:Edit Plotting');


//Kelola User
Route::group(['middleware' => ['auth', 'permission:Kelola User']], function () {

    Route::post('masterdata/username', [UsernameController::class, 'handle'])->name('masterdata.username.handle');
    Route::get('masterdata/username', [UsernameController::class, 'index'])->name('masterdata.username.index');
});
Route::get('masterdata/username/create', [UsernameController::class, 'create'])
    ->name('master.username.create')->middleware('permission:Create User');
Route::delete('masterdata/username/{userid}/{companycode}', [UsernameController::class, 'destroy'])
    ->name('master.username.destroy')->middleware('permission:Hapus User');
Route::group(['middleware' => ['auth', 'permission:Edit User']], function () {
    Route::put('masterdata/username/update/{userid}/{companycode}', [UsernameController::class, 'update'])
        ->name('master.username.update');
    Route::get('masterdata/username/{userid}/{companycode}/edit', [UsernameController::class, 'edit'])
        ->name('master.username.edit');
});
Route::group(['middleware' => ['auth', 'permission:Hak Akses']], function () {
    Route::put('masterdata/username/access/{userid}', [UsernameController::class, 'setaccess'])
        ->name('masterdata.username.setaccess');
    Route::get('masterdata/username/{userid}/access', [UsernameController::class, 'access'])
        ->name('masterdata.username.access');
});

//Herbisida
Route::group(['middleware' => ['auth', 'permission:Herbisida']], function () {
    Route::get('masterdata/herbisida', [HerbisidaController::class, 'index'])->name('masterdata.herbisida.index');
    Route::post('masterdata/herbisida', [HerbisidaController::class, 'store'])->name('masterdata.herbisida.store');
    Route::get('masterdata/herbisida/group', [HerbisidaController::class, 'group'])->name('masterdata.herbisida.group');
    Route::get('masterdata/herbisida/items', function (\Illuminate\Http\Request $request) {
        return \App\Models\Herbisida::where('companycode', $request->companycode)
            ->select('itemcode', 'itemname')->orderBy('itemcode')->get();
    })->name('masterdata.herbisida.items'); // Route untuk mengambil itemcode & itemname (loadtems()) dalam array JSON
});
Route::match(['put', 'patch'], 'masterdata/herbisida/{companycode}/{itemcode}', [HerbisidaController::class, 'update'])->name('masterdata.herbisida.update')->middleware(['auth', 'permission:Edit Herbisida']);;
Route::delete('masterdata/herbisida/{companycode}/{itemcode}', [HerbisidaController::class, 'destroy'])->name('masterdata.herbisida.destroy')->middleware(['auth', 'permission:Hapus Herbisida']);;

//Dosis Herbisida
//Route::resource('masterdata/herbisida-dosage',HerbisidaDosageController::class,['as' => 'masterdata']);
Route::group(['middleware' => ['auth', 'permission:Dosis Herbisida']], function () {
    Route::get('masterdata/herbisida-dosage', [HerbisidaDosageController::class, 'index'])->name('masterdata.herbisida-dosage.index');
    Route::post('masterdata/herbisida-dosage', [HerbisidaDosageController::class, 'store'])->name('masterdata.herbisida-dosage.store');
});
Route::match(['put', 'patch'], 'masterdata/herbisida-dosage/{companycode}/{activitycode}/{itemcode}', [HerbisidaDosageController::class, 'update'])->name('masterdata.herbisida-dosage.update')->middleware(['auth', 'permission:Edit Dosis Herbisida']);;
Route::delete('masterdata/herbisida-dosage/{companycode}/{activitycode}/{itemcode}', [HerbisidaDosageController::class, 'destroy'])->name('masterdata.herbisida-dosage.destroy')->middleware(['auth', 'permission:Hapus Dosis Herbisida']);;

Route::resource('herbisida-dosage', HerbisidaDosageController::class);

//Route::group(['middleware' => ['auth', 'permission:Aktivitas']], function () {
Route::get('masterdata/aktivitas', [ActivityController::class, 'index'])->name('masterdata.aktivitas.index');
Route::post('masterdata/aktivitas', [ActivityController::class, 'store'])->name('masterdata.aktivitas.store');
Route::put('masterdata/aktivitas/{aktivitas}', [ActivityController::class, 'update'])->name('masterdata.aktivitas.update'); //->middleware('permission:Edit Aktivitas');
Route::delete('masterdata/aktivitas/{aktivitas}', [ActivityController::class, 'destroy'])->name('masterdata.aktivitas.destroy'); //->middleware('permission:Hapus Aktivitas');
//});

//Jabatan
Route::group(['middleware' => ['auth', 'permission:Jabatan']], function () {
    Route::get('masterdata/jabatan', [JabatanController::class, 'index'])->name('masterdata.jabatan.index');
    Route::post('masterdata/jabatan', [JabatanController::class, 'store'])->name('masterdata.jabatan.store');
});
Route::match(['put', 'patch'], 'masterdata/jabatan/{idjabatan}', [JabatanController::class, 'update'])->name('masterdata.jabatan.update')->middleware(['auth', 'permission:Edit Jabatan']);
Route::delete('masterdata/jabatan/{idjabatan}', [JabatanController::class, 'destroy'])->name('masterdata.jabatan.destroy')->middleware(['auth', 'permission:Hapus Jabatan']);

// Approval
Route::group(['middleware' => ['auth', 'permission:Approval']], function () {
    Route::get('masterdata/approval', [ApprovalController::class, 'index'])->name('masterdata.approval.index');
    Route::post('masterdata/approval', [ApprovalController::class, 'store'])->name('masterdata.approval.store');
});
Route::match(['put', 'patch'], 'masterdata/approval/{companycode}/{category}', [ApprovalController::class, 'update'])->name('masterdata.approval.update')->middleware(['auth', 'permission:Edit Approval']);
Route::delete('masterdata/approval/{companycode}/{category}', [ApprovalController::class, 'destroy'])->name('masterdata.approval.destroy')->middleware(['auth', 'permission:Hapus Approval']);


//Kategori
Route::group(['middleware' => ['auth', 'permission:Kategori']], function () {
    Route::get('masterdata/kategori', [KategoriController::class, 'index'])->name('masterdata.kategori.index');
    Route::post('masterdata/kategori', [KategoriController::class, 'store'])->name('masterdata.kategori.store');
});
Route::match(['put', 'patch'], 'masterdata/kategori/{kodekategori}', [KategoriController::class, 'update'])->middleware(['auth', 'permission:Edit Kategori'])->name('masterdata.kategori.update');
Route::delete('masterdata/kategori/{kodekategori}', [KategoriController::class, 'destroy'])->middleware(['auth', 'permission:Hapus Kategori'])->name('masterdata.kategori.destroy');



// Varietas
Route::group(['middleware' => ['auth', 'permission:Varietas']], function () {
    Route::get('masterdata/varietas', [VarietasController::class, 'index'])
        ->name('masterdata.varietas.index');
    Route::post('masterdata/varietas', [VarietasController::class, 'store'])
        ->name('masterdata.varietas.store');
});
Route::match(['put', 'patch'], 'masterdata/varietas/{kodevarietas}', [VarietasController::class, 'update'])
    ->middleware(['auth', 'permission:Edit Varietas'])
    ->name('masterdata.varietas.update');
Route::delete('masterdata/varietas/{kodevarietas}', [VarietasController::class, 'destroy'])
    ->middleware(['auth', 'permission:Hapus Varietas'])
    ->name('masterdata.varietas.destroy');


// Accounting
Route::group(['middleware' => ['auth', 'permission:Accounting']], function () {
    Route::get('masterdata/accounting', [AccountingController::class, 'index'])->name('masterdata.accounting.index');
    Route::post('masterdata/accounting', [AccountingController::class, 'store'])->name('masterdata.accounting.store');
});
Route::match(
    ['put', 'patch'],
    'masterdata/accounting/{activitycode}/{jurnalaccno}/{jurnalacctype}',
    [AccountingController::class, 'update']
)
    ->middleware(['auth', 'permission:Edit Accounting'])
    ->name('masterdata.accounting.update');
Route::delete(
    'masterdata/accounting/{activitycode}/{jurnalaccno}/{jurnalacctype}',
    [AccountingController::class, 'destroy']
)
    ->middleware(['auth', 'permission:Hapus Accounting'])
    ->name('masterdata.accounting.destroy');


// Master List  
Route::group(['middleware' => ['auth', 'permission:MasterList']], function () {
    Route::get('masterdata/master-list', [MasterListController::class, 'index'])->name('masterdata.master-list.index');
    Route::post('masterdata/master-list', [MasterListController::class, 'store'])->name('masterdata.master-list.store');
});
Route::match(['put', 'patch'], 'masterdata/master-list/{companycode}/{plot}', [MasterListController::class, 'update'])->name('masterdata.master-list.update')->middleware(['auth', 'permission:Edit MasterList']);
Route::delete('masterdata/master-list/{companycode}/{plot}', [MasterListController::class, 'destroy'])->name('masterdata.master-list.destroy')->middleware(['auth', 'permission:Hapus MasterList']);


// Batch
Route::group(['middleware' => ['auth', 'permission:Batch']], function () {
    Route::get('masterdata/batch', [BatchController::class, 'index'])->name('masterdata.batch.index');
    Route::post('masterdata/batch', [BatchController::class, 'store'])->name('masterdata.batch.store');
});
Route::match(['put', 'patch'], 'masterdata/batch/{batchno}', [BatchController::class, 'update'])->name('masterdata.batch.update')->middleware(['auth', 'permission:Edit Batch']);
Route::delete('masterdata/batch/{batchno}', [BatchController::class, 'destroy'])->name('masterdata.batch.destroy')->middleware(['auth', 'permission:Hapus Batch']);


// Mandor Routes
//Route::group(['middleware' => ['auth', 'permission:Mandor']], function () {
// List and Create
Route::get('masterdata/mandor', [MandorController::class, 'index'])
    ->name('masterdata.mandor.index');
Route::post('masterdata/mandor', [MandorController::class, 'store'])
    ->name('masterdata.mandor.store');
//});
Route::match(['put', 'patch'], 'masterdata/mandor/{companycode}/{id}', [MandorController::class, 'update'])
    ->name('masterdata.mandor.update'); //->middleware(['auth', 'permission:Edit Mandor']);
Route::delete('masterdata/mandor/{companycode}/{id}', [MandorController::class, 'destroy'])
    ->name('masterdata.mandor.destroy'); //->middleware(['auth', 'permission:Hapus Mandor']);


Route::get('masterdata/tenagakerja', [TenagaKerjaController::class, 'index'])
    ->name('masterdata.tenagakerja.index');
Route::post('masterdata/tenagakerja', [TenagaKerjaController::class, 'store'])
    ->name('masterdata.tenagakerja.store');
Route::match(['put', 'patch'], 'masterdata/tenagakerja/{companycode}/{id}', [TenagaKerjaController::class, 'update'])
    ->name('masterdata.tenagakerja.update');
Route::delete('masterdata/tenagakerja/{companycode}/{id}', [TenagaKerjaController::class, 'destroy'])
    ->name('masterdata.tenagakerja.destroy');

// Aplikasi Routes
Route::group(['middleware' => ['auth', 'permission:Menu']], function () {
    Route::get('aplikasi/menu', [MenuController::class, 'index'])->name('masterdata.menu.index');
    Route::post('aplikasi/menu', [MenuController::class, 'store'])->name('masterdata.menu.store');
});
Route::put('aplikasi/menu/{menuid}', [MenuController::class, 'update'])->middleware(['auth', 'permission:Edit Menu'])->name('masterdata.menu.update');
Route::delete('aplikasi/menu/{menuid}/{name}', [MenuController::class, 'destroy'])->middleware(['auth', 'permission:Hapus Menu'])->name('masterdata.menu.destroy');


Route::group(['middleware' => ['auth', 'permission:Submenu']], function () {
    Route::get('aplikasi/submenu', [SubmenuController::class, 'index'])->name('masterdata.submenu.index');
    Route::post('aplikasi/submenu', [SubmenuController::class, 'store'])->name('masterdata.submenu.store');
});
Route::put('aplikasi/submenu/{submenuid}', [SubmenuController::class, 'update'])->middleware(['auth', 'permission:Edit Submenu'])->name('masterdata.submenu.update');
Route::delete('aplikasi/submenu/{submenuid}/{name}', [SubmenuController::class, 'destroy'])->middleware(['auth', 'permission:Hapus Submenu'])->name('masterdata.submenu.destroy');

Route::group(['middleware' => ['auth', 'permission:Subsubmenu']], function () {
    Route::get('aplikasi/subsubmenu', [SubsubmenuController::class, 'index'])->name('masterdata.subsubmenu.index');
    Route::post('aplikasi/subsubmenu', [SubsubmenuController::class, 'store'])->name('masterdata.subsubmenu.store');
});
Route::put('aplikasi/subsubmenu/{subsubmenuid}', [SubsubmenuController::class, 'update'])->middleware(['auth', 'permission:Edit Subsubmenu'])->name('masterdata.subsubmenu.update');
Route::delete('aplikasi/subsubmenu/{subsubmenuid}/{name}', [SubsubmenuController::class, 'destroy'])->middleware(['auth', 'permission:Hapus Subsubmenu'])->name('masterdata.subsubmenu.destroy');



// Upah Routes
Route::group(['middleware' => ['auth', 'permission:Upah']], function () {
    Route::get('masterdata/upah', [UpahController::class, 'index'])->name('masterdata.upah.index');
    Route::post('masterdata/upah', [UpahController::class, 'store'])->name('masterdata.upah.store');
});

Route::put('masterdata/upah/{id}', [UpahController::class, 'update'])
    ->middleware(['auth', 'permission:Edit Upah'])
    ->name('masterdata.upah.update');

Route::delete('masterdata/upah/{id}', [UpahController::class, 'destroy'])
    ->middleware(['auth', 'permission:Hapus Upah'])
    ->name('masterdata.upah.destroy');



// Kendaraan Routes
Route::group(['middleware' => ['auth', 'permission:Kendaraan']], function () {
    Route::get('masterdata/kendaraan', [KendaraanController::class, 'index'])->name('masterdata.kendaraan.index');
    Route::post('masterdata/kendaraan', [KendaraanController::class, 'handle'])->name('masterdata.kendaraan.handle');
});
Route::put('masterdata/kendaraan/{companycode}/{nokendaraan}', [KendaraanController::class, 'update'])
    ->middleware(['auth', 'permission:Edit Kendaraan'])
    ->name('masterdata.kendaraan.update');
Route::delete('masterdata/kendaraan/{companycode}/{nokendaraan}', [KendaraanController::class, 'destroy'])
    ->middleware(['auth', 'permission:Hapus Kendaraan'])
    ->name('masterdata.kendaraan.destroy');






// =============================================================================
// USER MANAGEMENT ROUTES - New Permission System
// =============================================================================

// Main User Management Routes
Route::group(['middleware' => ['auth', 'permission:Kelola User']], function () {
    
    // User CRUD - Main user management
    Route::get('usermanagement/user', [UserManagementController::class, 'userIndex'])
        ->name('usermanagement.user.index');
    Route::post('usermanagement/user', [UserManagementController::class, 'userStore'])
        ->name('usermanagement.user.store');
    Route::get('usermanagement/user/create', [UserManagementController::class, 'userCreate'])
        ->name('usermanagement.user.create');
    
    // User Company Access Management
    Route::get('usermanagement/user-company-permissions', [UserManagementController::class, 'userCompanyIndex'])
        ->name('usermanagement.usercompany.index');
    Route::post('usermanagement/user-company-permissions', [UserManagementController::class, 'userCompanyStore'])
        ->name('usermanagement.usercompany.store');
    Route::post('usermanagement/user-company-permissions/assign', [UserManagementController::class, 'userCompanyAssign'])
        ->name('usermanagement.usercompany.assign-companies');
        
    // User Specific Permission Overrides
    Route::get('usermanagement/user-permissions', [UserManagementController::class, 'userPermissionIndex'])
        ->name('usermanagement.userpermission.index');
    Route::post('usermanagement/user-permissions', [UserManagementController::class, 'userPermissionStore'])
        ->name('usermanagement.userpermission.store');
});

// User Edit/Update - dengan permission khusus
Route::group(['middleware' => ['auth', 'permission:Edit User']], function () {
    Route::get('usermanagement/user/{userid}/edit', [UserManagementController::class, 'userEdit'])
        ->name('usermanagement.user.edit');
    Route::put('usermanagement/user/{userid}', [UserManagementController::class, 'userUpdate'])
        ->name('usermanagement.user.update');
        
    // ✅ ADDED: User Company Access Delete
    Route::delete('usermanagement/user-company/{userid}/{companycode}', [UserManagementController::class, 'userCompanyDestroy'])
        ->name('usermanagement.usercompany.destroy');
});

// User Delete - dengan permission khusus  
Route::delete('usermanagement/user/{userid}', [UserManagementController::class, 'userDestroy'])
    ->name('usermanagement.user.destroy')
    ->middleware('permission:Hapus User');

// ✅ ADDED: User Permission Override Delete
Route::delete('usermanagement/user-permission/{userid}/{companycode}/{permission}', [UserManagementController::class, 'userPermissionDestroy'])
    ->name('usermanagement.userpermission.destroy')
    ->middleware('permission:Edit User');

// Permission Master Data Routes
Route::group(['middleware' => ['auth', 'permission:Master']], function () {
    
    // Permission Master CRUD
    Route::get('usermanagement/permissions-masterdata', [UserManagementController::class, 'permissionIndex'])
        ->name('usermanagement.permission.index');
    Route::post('usermanagement/permissions-masterdata', [UserManagementController::class, 'permissionStore'])
        ->name('usermanagement.permission.store');
    Route::put('usermanagement/permissions-masterdata/{permissionid}', [UserManagementController::class, 'permissionUpdate'])
        ->name('usermanagement.permission.update');
    Route::delete('usermanagement/permissions-masterdata/{permissionid}', [UserManagementController::class, 'permissionDestroy'])
        ->name('usermanagement.permission.destroy');
});

// Jabatan Permission Management Routes
Route::group(['middleware' => ['auth', 'permission:Jabatan']], function () {
    
    // Jabatan Permission Management
    Route::get('usermanagement/jabatan', [UserManagementController::class, 'jabatanPermissionIndex'])
        ->name('usermanagement.jabatan.index');
    Route::post('usermanagement/jabatan/assign-permission', [UserManagementController::class, 'jabatanPermissionStore'])
        ->name('usermanagement.jabatan.assign-permission');
    Route::delete('usermanagement/jabatan/remove-permission', [UserManagementController::class, 'jabatanPermissionDestroy'])
        ->name('usermanagement.jabatan.remove-permission');
    Route::post('/usermanagement/jabatan', [UserManagementController::class, 'jabatanStore'])
        ->name('usermanagement.jabatan.store');
    Route::put('/usermanagement/jabatan/{idjabatan}', [UserManagementController::class, 'jabatanUpdate'])
        ->name('usermanagement.jabatan.update');
    Route::delete('/usermanagement/jabatan/{idjabatan}', [UserManagementController::class, 'jabatanDestroy'])
        ->name('usermanagement.jabatan.destroy');
});

// =============================================================================
// API ROUTES - untuk AJAX calls dan datatables
// =============================================================================
Route::middleware(['auth'])->prefix('api/usermanagement')->group(function () {
    // Get jabatan permissions - untuk form jabatan
    Route::get('/jabatan/{idjabatan}/permissions', [App\Http\Controllers\MasterData\UserManagementController::class, 'getJabatanPermissions'])
        ->name('api.usermanagement.jabatan.permissions');
});

// =============================================================================
// PERMISSION API ROUTES - untuk modal permission
// =============================================================================
Route::get('usermanagement/user/{userid}/permissions-api', [UserManagementController::class, 'getUserPermissionsSimple'])
    ->middleware('auth')
    ->name('usermanagement.user.permissions-api');

// =============================================================================
// PERMISSION CHECKING UTILITY ROUTES - untuk testing dan debug
// =============================================================================
Route::group(['middleware' => ['auth', 'permission:Master']], function () {
    Route::get('usermanagement/test-permission/{userid}/{permission}', [UserManagementController::class, 'testUserPermission'])
        ->name('usermanagement.test-permission');
});

// =============================================================================
// SUPPORT TICKET ROUTES
// =============================================================================

Route::group(['middleware' => ['auth', 'permission:Kelola User']], function () {
    
    // Ticket Management (Admin)
    Route::get('usermanagement/support-ticket', [UserManagementController::class, 'ticketIndex'])
        ->name('usermanagement.ticket.index');
    Route::put('usermanagement/support-ticket/{ticket_id}', [UserManagementController::class, 'ticketUpdate'])
        ->name('usermanagement.ticket.update');
    Route::delete('usermanagement/support-ticket/{ticket_id}', [UserManagementController::class, 'ticketDestroy'])
        ->name('usermanagement.ticket.destroy');
});

// Public ticket submission (no auth required - for forgot password)
Route::post('support-ticket/submit', [UserManagementController::class, 'ticketStore'])
    ->name('support.ticket.submit');