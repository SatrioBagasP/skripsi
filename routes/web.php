<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TestingTwilloController;
use App\Http\Controllers\Dashboard\DashboardController;

Route::get('',[TestingTwilloController::class,'index']);

// Route::group

Route::controller(AuthController::class)->group(function () {
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'login')->name('postlogin');
    });
    Route::post('/logout', 'logout')->middleware(['auth'])->name('logout');
});

Route::middleware(['auth'])->group(function(){
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});