<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Dosen\DosenController;
use App\Http\Controllers\TestingTwilloController;
use App\Http\Controllers\Jurusan\JurusanController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Mahasiswa\MahasiswaController;
use App\Http\Controllers\UnitKemahasiswaan\UnitKemahasiswaanController;

// Route::get('',[TestingTwilloController::class,'index']);

// Route::group

Route::controller(AuthController::class)->group(function () {
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'login')->name('postlogin');
    });
    Route::post('/logout', 'logout')->middleware(['auth'])->name('logout');
});

Route::middleware(['auth'])->group(function(){

    // Master
    Route::prefix('/master')->name('master.')->group(function (){
        Route::prefix('/unit-kemahasiswaan')->name('unit-kemahasiswaan.')->controller(UnitKemahasiswaanController::class)->group(function (){
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });
        Route::prefix('/dosen')->name('dosen.')->controller(DosenController::class)->group(function (){
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });
        Route::prefix('/mahasiswa')->name('mahasiswa.')->controller(MahasiswaController::class)->group(function (){
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });
        Route::prefix('/jurusan')->name('jurusan.')->controller(JurusanController::class)->group(function (){
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });
        Route::prefix('/user')->name('user.')->controller(UserController::class)->group(function (){
            Route::get('/', 'index')->name('index');
        });
    });

    Route::prefix('/dashboard')->name('dashboard.')->controller(DashboardController::class)->group(function (){
        Route::get('/', 'index')->name('index');
    });
});