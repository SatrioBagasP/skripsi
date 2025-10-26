<?php

use App\Http\Controllers\Akademik\AkademikController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Dosen\DosenController;
use App\Http\Controllers\Jurusan\JurusanController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Jabatan\JabatanController;
use App\Http\Controllers\LaporanKegiatan\ApprovalLaporanKegiatanController;
use App\Http\Controllers\LaporanKegiatan\LaporanKegiatanController;
use App\Http\Controllers\Mahasiswa\MahasiswaController;
use App\Http\Controllers\Proposal\ApprovalProposalController;
use App\Http\Controllers\Proposal\ProposalController;
use App\Http\Controllers\Proposal\TrackingProposalController;
use App\Http\Controllers\Ruangan\RuanganController;
use App\Http\Controllers\UnitKemahasiswaan\UnitKemahasiswaanController;
use App\Models\LaporanKegiatan;
use App\Models\Ruangan;

// Route::get('',[TestingTwilloController::class,'index']);

// Route::group

Route::controller(AuthController::class)->group(function () {
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'login')->name('postlogin');
    });
    Route::post('/logout', 'logout')->middleware(['auth'])->name('logout');
});

Route::middleware(['auth', 'activeUser'])->group(function () {

    // Master
    Route::prefix('/master')->name('master.')->middleware('can:admin')->group(function () {
        Route::prefix('/unit-kemahasiswaan')->name('unit-kemahasiswaan.')->controller(UnitKemahasiswaanController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });
        Route::prefix('/dosen')->name('dosen.')->controller(DosenController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });
        Route::prefix('/mahasiswa')->name('mahasiswa.')->controller(MahasiswaController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });

        Route::prefix('/jabatan')->name('jabatan.')->controller(JabatanController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });

        Route::prefix('/akademik')->name('akademik.')->controller(AkademikController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });

        Route::prefix('/ruangan')->name('ruangan.')->controller(RuanganController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });

        Route::prefix('/jurusan')->name('jurusan.')->controller(JurusanController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });
        Route::prefix('/user')->name('user.')->controller(UserController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::get('/get-data', 'getData')->name('getData');
        });
    });

    Route::prefix('/')->name('dashboard.')->controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('index');
    });

    Route::middleware(['canAny:admin,unit-kemahasiswaan'])->group(function () {
        Route::prefix('/proposal')->name('proposal.')->controller(ProposalController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/all-option', 'getOption')->name('getOption');
            // Route::get('/get-ruangan-option', 'getRuanganOption')->name('getRuanganOption');
            Route::get('/create', 'create')->name('create');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::post('/delete', 'delete')->name('delete');
            Route::post('/pengajuan', 'pengajuan')->name('pengajuan');
            Route::get('/get-data', 'getData')->name('getData');
        });

        Route::get('/get-ruangan-option', [RuanganController::class, 'getRuanganOption'])->name('getRuanganOption');


        Route::prefix('/laporan-kegiatan')->name('laporan-kegiatan.')->controller(LaporanKegiatanController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/all-option', 'getOption')->name('getOption');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update', 'update')->name('update');
            Route::delete('/delete-image', 'deleteImage')->name('delete-image');
            Route::post('/pengajuan', 'pengajuan')->name('pengajuan');
            Route::get('/get-data', 'getData')->name('getData');
        });
    });



    Route::middleware(['canAny:admin,verifikator'])->group(function () {

        Route::prefix('/approval-proposal')->name('approval-proposal.')->controller(ApprovalProposalController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/approval-dosen', 'approvalDosen')->name('approvalDosen');
            Route::post('/approval-kaprodi', 'approvalKaprodi')->name('approvalKaprodi');
            Route::post('/approval-minat-bakat', 'approvalMinatBakat')->name('approvalMinatBakat');
            Route::post('/approval-layanan-mahasiswa', 'approvalLayananMahasiswa')->name('approvalLayananMahasiswa');
            Route::post('/approval-wakil-rektor', 'approvalWakilRektor')->name('approvalWakilRektor');
            Route::get('/get-data', 'getData')->name('getData');
        });

        Route::prefix('/approval-laporan-kegiatan')->name('approval-laporan-kegiatan.')->controller(ApprovalLaporanKegiatanController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/approval-dosen', 'approvalDosen')->name('approvalDosen');
            Route::post('/approval-kaprodi', 'approvalKaprodi')->name('approvalKaprodi');
            Route::post('/approval-minat-bakat', 'approvalMinatBakat')->name('approvalMinatBakat');
            Route::post('/approval-layanan-mahasiswa', 'approvalLayananMahasiswa')->name('approvalLayananMahasiswa');
            Route::post('/approval-wakil-rektor', 'approvalWakilRektor')->name('approvalWakilRektor');
            Route::get('/get-data', 'getData')->name('getData');
        });
    });


    Route::prefix('/tracking')->name('tracking.')->controller(TrackingProposalController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/tracking/search',  'search')->name('search');
    });
});
