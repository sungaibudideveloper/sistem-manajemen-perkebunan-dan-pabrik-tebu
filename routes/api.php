<?php

use App\Http\Controllers\Api\PerhitunganUpahApiMobile;
use App\Http\Controllers\Api\Timbangan;
use App\Http\Controllers\MobileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Mobile & External Applications
|--------------------------------------------------------------------------
|
| Routes for mobile apps and external API consumers.
| Uses token-based authentication (Laravel Sanctum).
| All routes are prefixed with /api automatically.
|
*/

// Public endpoints - no authentication required
Route::post('/loginmobile', [MobileController::class, 'loginMobile']);
Route::get('/getcompanymobile', [MobileController::class, 'getCompaniesMobile']);
Route::get('/test-api', function() {
    return response()->json([
        'message' => 'API works', 
        'time' => now(),
        'version' => '1.0.0'
    ]);
});

// Protected endpoints - requires Sanctum token authentication
Route::middleware('auth:sanctum')->group(function () {
    
    // Mobile wage calculation and insertion
    Route::post('/mobile/insert-upah-tenaga-kerja', [PerhitunganUpahApiMobile::class, 'insertWorkerWage'])
        ->name('api.mobile.insert-upah');
    
    // Agronomi and HPT data submission
    Route::post('/agronomistoremobile', [MobileController::class, 'storeMobileAgronomi']);
    Route::post('/hptstoremobile', [MobileController::class, 'storeMobileHPT']);
    Route::post('/getfieldbymapping', [MobileController::class, 'getFieldByMapping']);
    Route::post('/checkdataagronomi', [MobileController::class, 'checkDataAgronomi']);
    Route::post('/checkdatahpt', [MobileController::class, 'checkDataHPT']);
    
    // Timbangan data insertion
    Route::post('/timbangan/dev/v1/insertdata', [Timbangan::class, 'insertData']);
});