<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\userController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

Route::get('/', [LoginController::class,'index'])->name('login');
Route::post('/authenticate', [LoginController::class,'authenticate'])->name('login.authenticate');

Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard.index');
Route::get('/setting', [SettingController::class,'index'])->name('setting.index');
Route::put('/setting/{setting}/update', [SettingController::class,'update'])->name('setting.update');


Route::resource('user', userController::class);