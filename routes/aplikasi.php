<?php

use App\Http\Controllers\Aplikasi\MenuController;

Route::group(['middleware' => ['auth', 'permission:Menu']], function () {
    Route::any('aplikasi/menu/update', [MenuController::class, 'update'])->name('aplikasi.menu.update');
    Route::any('aplikasi/menu/insert', [MenuController::class, 'insert'])->name('aplikasi.menu.insert');
    Route::any('aplikasi/menu/delete', [MenuController::class, 'delete'])->name('aplikasi.menu.delete');
});
