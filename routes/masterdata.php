<?php
use App\Http\Controllers\MasterData\ActivityController;
use App\Http\Controllers\MasterData\BlokController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\MappingController;
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

//company
Route::group(['middleware' => ['auth', 'permission:Company']], function () {
    Route::post('masterdata/company', [CompanyController::class, 'handle'])->name('master.company.handle');
    Route::get('masterdata/company', [CompanyController::class, 'index'])->name('master.company.index');
});
Route::put('masterdata/company/{companycode}', [CompanyController::class, 'update'])->name('master.company.update')->middleware('permission:Edit Company');
Route::delete('masterdata/company/{companycode}', [CompanyController::class, 'destroy'])
    ->name('master.company.destroy')->middleware('permission:Hapus Company');

//Blok
Route::group(['middleware' => ['auth', 'permission:Blok']], function () {
    Route::get('masterdata/blok', [BlokController::class, 'index'])->name('master.blok.index');
    Route::post('masterdata/blok', [BlokController::class, 'handle'])->name('master.blok.handle');
});
Route::delete('masterdata/blok/{blok}/{companycode}', [BlokController::class, 'destroy'])
    ->name('master.blok.destroy')->middleware('permission:Hapus Blok');
Route::put('masterdata/blok/{blok}/{companycode}', [BlokController::class, 'update'])
    ->name('master.blok.update')->middleware('permission:Edit Blok');

//Plotting
Route::group(['middleware' => ['auth', 'permission:Plotting']], function () {
    Route::post('masterdata/plotting', [PlottingController::class, 'handle'])->name('master.plotting.handle');
    Route::get('masterdata/plotting', [PlottingController::class, 'index'])->name('master.plotting.index');
});
Route::delete('masterdata/plotting/{plot}/{companycode}', [PlottingController::class, 'destroy'])
    ->name('master.plotting.destroy')->middleware('permission:Hapus Plotting');
Route::put('masterdata/plotting/{plot}/{companycode}', [PlottingController::class, 'update'])
    ->name('master.plotting.update')->middleware('permission:Edit Plotting');

//Mapping
Route::group(['middleware' => ['auth', 'permission:Mapping']], function () {
    Route::post('masterdata/mapping/get-filtered-data', [MappingController::class, 'getFilteredData'])->name('get.filtered.data');
    Route::post('masterdata/mapping', [MappingController::class, 'handle'])->name('master.mapping.handle');
    Route::get('masterdata/mapping', [MappingController::class, 'index'])->name('master.mapping.index');
});
Route::delete('masterdata/mapping/{plotcodesample}/{blok}/{plot}/{companycode}', [MappingController::class, 'destroy'])
    ->name('master.mapping.destroy')->middleware('permission:Hapus Mapping');
Route::put('masterdata/mapping/{plotcodesample}/{blok}/{plot}/{companycode}', [MappingController::class, 'update'])
    ->name('master.mapping.update')->middleware('permission:Edit Mapping');



//Kelola User
Route::group(['middleware' => ['auth', 'permission:Kelola User']], function () {

    Route::post('masterdata/username', [UsernameController::class, 'handle'])->name('master.username.handle');
    Route::get('masterdata/username', [UsernameController::class, 'index'])->name('master.username.index');
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
        ->name('master.username.setaccess');
    Route::get('masterdata/username/{userid}/access', [UsernameController::class, 'access'])
        ->name('master.username.access');
});


//Herbisida
Route::group(['middleware' => ['auth', 'permission:Herbisida']], function () {
    Route::get('masterdata/herbisida', [HerbisidaController::class, 'index'])->name('masterdata.herbisida.index');
    Route::post('masterdata/herbisida', [HerbisidaController::class, 'store'])->name('masterdata.herbisida.store');
    Route::get('masterdata/herbisida/group', [HerbisidaController::class, 'group'])->name('masterdata.herbisida.group');
    Route::get('masterdata/herbisida/items', function (\Illuminate\Http\Request $request) {
        return \App\Models\Herbisida::where('companycode', $request->companycode)
            ->select('itemcode','itemname')->orderBy('itemcode')->get();})->name('masterdata.herbisida.items'); // Route untuk mengambil itemcode & itemname (loadtems()) dalam array JSON
});
Route::match(['put', 'patch'], 'masterdata/herbisida/{companycode}/{itemcode}',[HerbisidaController::class, 'update'])->name('masterdata.herbisida.update')->middleware(['auth','permission:Edit Herbisida']);;
Route::delete('masterdata/herbisida/{companycode}/{itemcode}',[HerbisidaController::class, 'destroy'])->name('masterdata.herbisida.destroy')->middleware(['auth','permission:Hapus Herbisida']);;

//Dosis Herbisida
//Route::resource('masterdata/herbisida-dosage',HerbisidaDosageController::class,['as' => 'masterdata']);
Route::group(['middleware' => ['auth', 'permission:Dosis Herbisida']], function () {
    Route::get('masterdata/herbisida-dosage', [HerbisidaDosageController::class, 'index'])->name('masterdata.herbisida-dosage.index');
    Route::post('masterdata/herbisida-dosage', [HerbisidaDosageController::class, 'store'])->name('masterdata.herbisida-dosage.store');

});
Route::match(['put', 'patch'], 'masterdata/herbisida-dosage/{companycode}/{activitycode}/{itemcode}',[HerbisidaDosageController::class, 'update'])->name('masterdata.herbisida-dosage.update')->middleware(['auth','permission:Edit Dosis Herbisida']);;
Route::delete('masterdata/herbisida-dosage/{companycode}/{activitycode}/{itemcode}',[HerbisidaDosageController::class, 'destroy'])->name('masterdata.herbisida-dosage.destroy')->middleware(['auth','permission:Hapus Dosis Herbisida']);;

Route::resource('herbisida-dosage', HerbisidaDosageController::class);

//Route::group(['middleware' => ['auth', 'permission:Aktivitas']], function () {
    Route::get('masterdata/aktivitas', [ActivityController::class, 'index'])->name('master.aktivitas.index');
    Route::post('masterdata/aktivitas', [ActivityController::class, 'store'])->name('master.aktivitas.store');
    Route::put('masterdata/aktivitas/{aktivitas}', [ActivityController::class, 'update'])->name('master.aktivitas.update');//->middleware('permission:Edit Aktivitas');
    Route::delete('masterdata/aktivitas/{aktivitas}', [ActivityController::class, 'destroy'])->name('master.aktivitas.destroy');//->middleware('permission:Hapus Aktivitas');
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
Route::match(['put', 'patch'], 'masterdata/approval/{companycode}/{activitycode}', [ApprovalController::class, 'update'])->name('masterdata.approval.update')->middleware(['auth', 'permission:Edit Approval']);
Route::delete('masterdata/approval/{companycode}/{activitycode}', [ApprovalController::class, 'destroy'])->name('masterdata.approval.destroy')->middleware(['auth', 'permission:Hapus Approval']);


//Kategori
Route::group(['middleware' => ['auth', 'permission:Kategori']], function () {
    Route::get('masterdata/kategori', [KategoriController::class, 'index'])->name('masterdata.kategori.index');
    Route::post('masterdata/kategori', [KategoriController::class, 'store'])->name('masterdata.kategori.store');
});
Route::match(['put','patch'], 'masterdata/kategori/{kodekategori}',[KategoriController::class,'update'])->middleware(['auth','permission:Edit Kategori'])->name('masterdata.kategori.update');
Route::delete('masterdata/kategori/{kodekategori}',[KategoriController::class,'destroy'])->middleware(['auth','permission:Hapus Kategori'])->name('masterdata.kategori.destroy');



// Varietas
Route::group(['middleware' => ['auth', 'permission:Varietas']], function () {
    Route::get('masterdata/varietas', [VarietasController::class, 'index'])
        ->name('masterdata.varietas.index');
    Route::post('masterdata/varietas', [VarietasController::class, 'store'])
        ->name('masterdata.varietas.store');
});
Route::match(['put','patch'], 'masterdata/varietas/{kodevarietas}', [VarietasController::class, 'update'])
    ->middleware(['auth','permission:Edit Varietas'])
    ->name('masterdata.varietas.update');
Route::delete('masterdata/varietas/{kodevarietas}', [VarietasController::class, 'destroy'])
    ->middleware(['auth','permission:Hapus Varietas'])
    ->name('masterdata.varietas.destroy');


// Accounting
Route::group(['middleware' => ['auth','permission:Accounting']], function() {
    Route::get   ('masterdata/accounting',[AccountingController::class,'index'])->name('masterdata.accounting.index');
    Route::post  ('masterdata/accounting',[AccountingController::class,'store'])->name('masterdata.accounting.store');
});
Route::match(['put','patch'],
    'masterdata/accounting/{activitycode}/{jurnalaccno}/{jurnalacctype}',
    [AccountingController::class,'update'])
    ->middleware(['auth','permission:Edit Accounting'])
    ->name('masterdata.accounting.update');
Route::delete(
    'masterdata/accounting/{activitycode}/{jurnalaccno}/{jurnalacctype}',
    [AccountingController::class,'destroy'])
    ->middleware(['auth','permission:Hapus Accounting'])
    ->name('masterdata.accounting.destroy');


//Route::group(['middleware' => ['auth','permission:MasterList']], function() {
    Route::get('masterdata/master-list',[MasterListController::class,'index'])->name('master.master-list.index');
//});


// Mandor Routes
Route::group(['middleware' => ['auth', 'permission:Mandor']], function () {
    // List and Create
    Route::get('masterdata/mandor', [MandorController::class, 'index'])
         ->name('masterdata.mandor.index');
    Route::post('masterdata/mandor', [MandorController::class, 'store'])
         ->name('masterdata.mandor.store');
});
Route::match(['put', 'patch'], 'masterdata/mandor/{companycode}/{id}', [MandorController::class, 'update'])
     ->name('masterdata.mandor.update')
     ->middleware(['auth', 'permission:Edit Mandor']);
Route::delete('masterdata/mandor/{companycode}/{id}', [MandorController::class, 'destroy'])
     ->name('masterdata.mandor.destroy')
     ->middleware(['auth', 'permission:Hapus Mandor']);
