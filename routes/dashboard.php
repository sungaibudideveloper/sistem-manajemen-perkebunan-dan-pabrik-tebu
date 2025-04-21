<?php

use App\Http\Controllers\Dashboard\DashboardController;

Route::match(['POST', 'GET'], 'dashboard/agronomi',  [DashboardController::class, 'agronomi'])
    ->name('dashboard.agronomi')->middleware('permission:Dashboard Agronomi');
Route::match(['POST', 'GET'], 'dashboard/hpt',  [DashboardController::class, 'hpt'])
    ->name('dashboard.hpt')->middleware('permission:Dashboard HPT');
