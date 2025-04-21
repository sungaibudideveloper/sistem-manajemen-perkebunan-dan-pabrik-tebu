<?php
use App\Http\Controllers\MasterData\BlokController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\MappingController;
use App\Http\Controllers\MasterData\PlottingController;
use App\Http\Controllers\MasterData\UsernameController;
use App\Http\Controllers\MasterData\HerbisidaController;
use App\Http\Controllers\MasterData\HerbisidaDosageController;

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



Route::group(['middleware' => ['auth', 'permission:Herbisida']], function () {
    Route::post('masterdata/herbisida', [HerbisidaController::class, 'store'])->name('master.herbisida.handle');
    Route::get('masterdata/herbisida', [HerbisidaController::class, 'index'])->name('master.herbisida.index');
    Route::put('masterdata/herbisida/{itemcode}', [HerbisidaController::class, 'update'])->name('master.herbisida.update')->middleware('permission:Edit Herbisida');
    Route::delete('masterdata/herbisida/{itemcode}', [HerbisidaController::class, 'destroy'])->name('master.herbisida.destroy')->middleware('permission:Hapus Herbisida');
});

//Dosis Herbisida
Route::resource('masterdata/herbisida-dosage',HerbisidaDosageController::class,['as' => 'masterdata']);