<?php

use App\Http\Controllers\Api\UjianTAController;
use App\Models\Bimbingan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\SesiController;
use App\Http\Controllers\Api\RuangController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\SemesterController;
use App\Http\Controllers\Api\BimbinganController;
use App\Http\Controllers\Api\TugasAkhirController;
use App\Http\Controllers\Auth\AuthAdminController;
use App\Http\Controllers\Auth\AuthDosenController;
use App\Http\Controllers\Api\TahunAjaranController;
use App\Http\Controllers\Api\DosenPengujiController;
use App\Http\Controllers\Dosens\Dosen\DosenController;
use App\Http\Controllers\Dosens\Kalab\KalabController;
use App\Http\Controllers\Mahasiswa\MahasiswaController;
use App\Http\Controllers\Admin\AdminTa\AdminTaController;
use App\Http\Controllers\Api\RequestPembimbingController;
use App\Http\Controllers\Dosens\Kaprodi\KaprodiController;
use App\Http\Controllers\Dosens\Petugas\PetugasController;
use App\Http\Controllers\Admin\Superadmin\SuperadminController;
use App\Http\Controllers\Admin\AdminPengelola\AdminPengelolaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// filepath: /c:/laragon/www/Backend-Port/routes/api.php




// route sanctum global
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/logout', [UserController::class, 'logoutUser']);
});




Route::post('/register/mahasiswa', [AuthController::class, 'registerUser']);
Route::post('/login/mahasiswa', [AuthController::class, 'loginUser'])->name('login.mahasiswa');


Route::post('/mahasiswa/forgot-password', [UserController::class, 'sendResetLinkMahasiswa']);
Route::post('/mahasiswa/reset-password', [UserController::class, 'resetPasswordMahasiswa']);

Route::post('/dosen/forgot-password', [UserController::class, 'sendResetLinkDosen']);
Route::post('/dosen/reset-password', [UserController::class, 'resetPasswordDosen']);

Route::post('/admin/forgot-password', [UserController::class, 'sendResetLinkAdmin']);
Route::post('/admin/reset-password', [UserController::class, 'resetPasswordAdmin']);

// ---------- || Route Mahasiswa || ---------- \\

Route::middleware(['auth:sanctum', 'mahasiswa'])->group(function () {

    Route::get('mahasiswa/dashboard', [MahasiswaController::class, 'dashboard']);
    Route::get('mahasiswa/profile', [MahasiswaController::class, 'getProfileMahasiswa']);
    Route::put('mahasiswa/profile/edit', [MahasiswaController::class, 'editProfileMahasiswa']);

    //request pembimbing
    Route::post('/mahasiswa/request-pembimbing/', [RequestPembimbingController::class, 'storeWithTugasAkhir']);

    //proposal
    Route::post('/mahasiswa/proposal/', [ProposalController::class, 'storeForMhs']);
    Route::put('/mahasiswa/proposal/{id}', [ProposalController::class, 'updateForMhs']);

    // Bimbingan
    Route::get('/mahasiswa/bimbingan', [BimbinganController::class, 'getBimbinganByMahasiswa']);
    Route::post('/mahasiswa/bimbingan/', [BimbinganController::class, 'storeBimbinganForMahasiswa']);

    //Tugas Akhir
    Route::put('/mahasiswa/tugas-akhir/{id}/daftar', [TugasAkhirController::class, 'updateTugasAkhirForMahasiswa']);

});




// ---------- || Route Dosen || ---------- \\

Route::post('/login/dosen', [AuthDosenController::class, 'loginDosen'])->name('login.dosen');

// Route Global Dosen
Route::middleware(['auth:sanctum', 'dosen'])->group(function () {


    Route::post('/dosen/switch_jabatan', [AuthDosenController::class, 'switchJabatan']);
    Route::get('/dosen/current_jabatan', [AuthDosenController::class, 'getCurrentJabatan']);
    Route::get('/dosen/jabatan', [AuthDosenController::class, 'getUserJabatan']);

    Route::get('/dosen/profil', [DosenController::class, 'getProfileDosen']);
    Route::put('/dosen/profil/edit', [DosenController::class, 'editProfileDosen']);

    // Sistem Tugas-akhir (Request Pembimbing)
    Route::get('/dosen/request-pembimbing/', [RequestPembimbingController::class, 'getByDosen']);
    Route::put('/dosen/request-pembimbing/approval/{id}', [RequestPembimbingController::class, 'updateApprovalForDosen']);

    // Sistem TugasAkhir (Bimbingan)
    Route::get('/dosen/bimbingan/', [BimbinganController::class, 'getTugasAkhirByDosen']);
    Route::put('/dosen/bimbingan/{id}', [BimbinganController::class, 'updateVerifikasiBimbinganByDosen']);
    Route::put('/dosen/bimbingan/{id}/nilai', [BimbinganController::class,'memberiNilaiTAbyDosen']);

    // Sistem Tugas akhir (Tugas Akhir)
    //blm ditest
    Route::put('/dosen/tugas-akhir/{id}/nilai', [TugasAkhirController::class, 'memberiNilaiTAbyDosen']);

    // Route Dosen Biasa
    Route::middleware(['auth:sanctum', 'jabatan.dosen:Dosen'])->group(function () {
        Route::get('/dosen/dashboard', [DosenController::class, 'dashboard']);
    });

    // Route Kalab
    Route::middleware(['auth:sanctum', 'jabatan.dosen:Kalab'])->group(function () {
        Route::get('/kalab/dashboard', [KalabController::class, 'dashboard']);
    });

    // Route Kaprodi
    Route::middleware(['auth:sanctum', 'jabatan.dosen:Kaprodi'])->group(function () {
        Route::get('/kaprodi/dashboard', [KaprodiController::class, 'dashboard']);
    });

    // Route Pelaporan
    Route::middleware(['auth:sanctum', 'jabatan.dosen:Petugas'])->group(function () {
        Route::get('/petugas/dashboard', [PetugasController::class, 'dashboard']);
    });


});




// ---------- || Route Admin || ---------- \\

Route::post('/login/admin', [AuthAdminController::class, 'loginAdmin']);

// Route Global Middleware Admin
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Route::post('/logout', [AuthController::class, 'logoutUser']);

    // Route Superadmin
    Route::middleware(['auth:sanctum', 'admin.role:superadmin'])->group(function () {

        // cuma superadmin yg boleh buat admin klo localan dipindah dlu ke public
        Route::post('/register/admin', [AuthAdminController::class, 'registerAdmin']);


        //Superadmin -> dosen
        Route::get('/superadmin/dashboard', [SuperadminController::class, 'dashboard']);
        Route::post('/superadmin/create_dosen', [SuperadminController::class, 'createDosen']);
        Route::get('/superadmin/dosen', [SuperadminController::class, 'getDosen']);
        Route::post('/superadmin/dosen/set_status', [SuperadminController::class, 'setStatusDosen']);
        Route::post('/superadmin/create_jabatan', [SuperadminController::class, 'createJabatan']);
        Route::post('/superadmin/insert_jabatan', [SuperadminController::class, 'insertJabatan']);
        Route::delete('/superadmin/delete_jabatan', [SuperadminController::class, 'deleteJabatan']);


        Route::get('/superadmin/mahasiswa', [SuperadminController::class, 'getMahasiswa']);
        // Route::get('/superadmin/mahasiswa_konfirmasi', [SuperadminController::class, 'getKonfirmasiMahasiswa']);
        // Route::post('/superadmin/mahasiswa/konfirmasi_mahasiswa', [SuperadminController::class, 'konfirmasiMahasiswa']);
        Route::post('/superadmin/mahasiswa/set_status', [SuperadminController::class, 'setStatusMahasiswa']);
    });

    // Route Admin TA
    Route::middleware(['auth:sanctum', 'admin.role:admin_ta'])->group(function () {
        Route::get('/admin_ta/dashboard', [AdminTaController::class, 'dashboard']);

        // Tugas-akhir
        Route::get('/admin_ta/tugas-akhir', [TugasAkhirController::class, 'indexForAdmin']);
        Route::put('/admin_ta/tugas-akhir/{id}/update-active-status', [TugasAkhirController::class, 'updateActiveStatusForAdmin']);
        Route::put('/admin_ta/tugas-akhir/{id}/update-valid-status', [TugasAkhirController::class, 'updateValidationStatusForAdmin']);
        Route::get('/admin_ta/tugas-akhir/daftar', [TugasAkhirController::class, 'getRegisterTAForAdmin']);


        // Plotting Pembimbing
        Route::post('/admin_ta/request-pembimbing/', [RequestPembimbingController::class, 'storeForAdmin']);

        // Route proposal
        Route::get('/admin_ta/proposal', [ProposalController::class, 'indexForAdmin']);
        Route::put('/admin_ta/proposal/{id}/status', [ProposalController::class, 'updateValidationStatusForAdmin']);
        Route::put('/admin_ta/proposal/{id}/nilai-catatan', [ProposalController::class, 'updateScoreandNotesForAdmin']);

        // Plotting penguji
        Route::get('/admin_ta/dosen-penguji', [DosenPengujiController::class, 'getReadyTAforAdmin']);
        Route::post('/admin_ta/dosen-penguji/', [DosenPengujiController::class, 'storeForAdmin']);
        Route::put('/admin_ta/dosen-penguji/{id}', [DosenPengujiController::class, 'updateForAdmin']);


        // Route Sesi
        Route::apiResource('/admin_ta/sesi', SesiController::class);

        // Route Ruang
        Route::apiResource('/admin_ta/ruang', RuangController::class);

        // Route Semester
        Route::get('/admin_ta/semester', [SemesterController::class, 'index']);
        Route::get('/admin_ta/semester/{id}', [SemesterController::class, 'show']);
        Route::put('/admin_ta/semester/{id}', [SemesterController::class, 'update']);

        // Route Tahun Ajaran
        Route::apiResource('/admin_ta/tahun-ajaran', TahunAjaranController::class);

        // Route Ujian TA
        Route::apiResource('/admin_ta/ujian-ta', UjianTAController::class);
        // Route::get('/admin_ta/ujian-ta', [UjianTAController::class, 'index']);
        // Route::post('/admin_ta/ujian-ta/', [UjianTAController::class, 'store']);
        // Route::put('/admin_ta/ujian-ta/{id}', [UjianTAController::class, 'update']);
        Route::get('/admin_ta/ujian-ta/daftar-ta', [UjianTAController::class, 'getTAReadyForAdmin']);
    });

    // Route Admin Pengelola (inventaris)
    Route::middleware(['auth:sanctum', 'admin.role:admin_pengelola'])->group(function () {
        //route laennya buat admin pengelola
        Route::get('/admin_pengelola/dashboard', [AdminPengelolaController::class, 'dashboard']);
    });
});