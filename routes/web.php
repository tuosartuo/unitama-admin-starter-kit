<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\userController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
Route::resource('user', userController::class);