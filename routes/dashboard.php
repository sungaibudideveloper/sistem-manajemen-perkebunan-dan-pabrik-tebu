<?php
use App\Http\Controllers\Dashboard\DashboardController;



// Option 2: Gunakan permission yang lebih spesifik
Route::match(['POST', 'GET'], 'dashboard/agronomi', [DashboardController::class, 'agronomi'])
    ->name('dashboard.agronomi')
    ->middleware('permission:Agronomi');

Route::match(['POST', 'GET'], 'dashboard/hpt', [DashboardController::class, 'hpt'])
    ->name('dashboard.hpt')
    ->middleware('permission:HPT');

