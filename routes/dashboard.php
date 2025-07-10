<?php
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\TimelineController;
use App\Http\Controllers\Dashboard\MapsController;



// Option 2: Gunakan permission yang lebih spesifik
Route::match(['POST', 'GET'], 'dashboard/agronomi', [DashboardController::class, 'agronomi'])
    ->name('dashboard.agronomi')
    ->middleware('permission:Agronomi');

Route::match(['POST', 'GET'], 'dashboard/hpt', [DashboardController::class, 'hpt'])
    ->name('dashboard.hpt')
    ->middleware('permission:HPT');

Route::match(['POST', 'GET'], 'dashboard/agronomi',  [DashboardController::class, 'agronomi'])
    ->name('dashboard.agronomi');//->middleware('permission:Dashboard Agronomi');
Route::match(['POST', 'GET'], 'dashboard/hpt',  [DashboardController::class, 'hpt'])
    ->name('dashboard.hpt');//->middleware('permission:Dashboard HPT');

Route::match(['POST', 'GET'], 'dashboard/timeline',  [TimelineController::class, 'index'])
    ->name('dashboard.timeline');//->middleware('permission:Dashboard HPT');

    Route::match(['POST', 'GET'], 'dashboard/maps',  [MapsController::class, 'index'])
    ->name('dashboard.maps');
    Route::match(['POST', 'GET'], 'dashboard/mapsapi',  [MapsController::class, 'indexapi'])
    ->name('dashboard.mapsapi');
    Route::match(['POST', 'GET'], 'dashboard/callmapsapi', [MapsController::class, 'callmapsapi'])
    ->name('dashboard.callmapsapi');

        Route::match(['POST', 'GET'], 'dashboard/maps/upload',  [MapsController::class, 'upload'])
            ->name('dashboard.maps.upload');
