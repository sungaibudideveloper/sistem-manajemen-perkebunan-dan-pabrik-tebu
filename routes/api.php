<?php

use App\Http\Controllers\MobileController;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Timbangan;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/loginmobile', [MobileController::class, 'loginMobile']);
Route::get('/getcompanymobile', [MobileController::class, 'getCompaniesMobile']);
Route::post('/agronomistoremobile', [MobileController::class, 'storeMobileAgronomi']);
Route::post('/hptstoremobile', [MobileController::class, 'storeMobileHPT']);
Route::post('/getfieldbymapping', [MobileController::class, 'getFieldByMapping']);
Route::post('/checkdataagronomi', [MobileController::class, 'checkDataAgronomi']);
Route::post('/checkdatahpt', [MobileController::class, 'checkDataHPT']);
Route::post('/checkdatahpt', [MobileController::class, 'checkDataHPT']);
Route::post('timbangan/dev/v1/insertdata', [Timbangan::class, 'insertData']);

Route::get('/test-api', function() {
    return response()->json(['message' => 'API works', 'time' => now()]);
});
