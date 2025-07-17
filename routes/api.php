<?php

use App\Http\Controllers\Admin\AdminPengelola\AdminPengelolaController;
use App\Http\Controllers\Admin\AdminPengelola\BhpController;
use App\Http\Controllers\Admin\AdminPengelola\GedungController;
use App\Http\Controllers\Admin\AdminPengelola\InventarisController;
use App\Http\Controllers\Admin\AdminPengelola\JenisInventarisController;
use App\Http\Controllers\Admin\AdminPengelola\LantaiController;
use App\Http\Controllers\Admin\AdminPengelola\MerkController;
use App\Http\Controllers\Admin\AdminPengelola\PeminjamanBhpController;
use App\Http\Controllers\Admin\AdminPengelola\PeminjamanInventarisController;
use App\Http\Controllers\Admin\AdminPengelola\RuangController;
use App\Http\Controllers\Admin\AdminPengelola\TipeController;
use App\Http\Controllers\Admin\AdminTa\AdminTaController;
use App\Http\Controllers\Admin\Superadmin\SuperadminController;
use App\Http\Controllers\Auth\AuthAdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AuthDosenController;
use App\Http\Controllers\Dosens\Dosen\DosenController;
use App\Http\Controllers\Dosens\Kalab\KalabController;
use App\Http\Controllers\Dosens\Kaprodi\KaprodiController;
use App\Http\Controllers\Dosens\Petugas\PetugasController;
use App\Http\Controllers\Mahasiswa\MahasiswaController;
use App\Http\Controllers\UserController;
use App\Models\AdminPengelola\Bhp;
use App\Models\JabatanDosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// route sanctum global
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/logout', [UserController::class, 'logoutUser']);
});

Route::middleware('throttle:7,1')->group(function () {
    Route::post('/login/mahasiswa', [AuthController::class, 'loginUser']);
    Route::post('/register/mahasiswa', [AuthController::class, 'registerUser']);
    
    Route::post('/login/dosen', [AuthDosenController::class, 'loginDosen']);
    Route::post('/login/admin', [AuthAdminController::class, 'loginAdmin']);
});
    
    Route::middleware('throttle:5,1')->group(function () {
    Route::post('/mahasiswa/forgot-password', [UserController::class, 'sendResetLinkMahasiswa']);
    Route::post('/mahasiswa/reset-password', [UserController::class, 'resetPasswordMahasiswa']);
    
    Route::post('/dosen/forgot-password', [UserController::class, 'sendResetLinkDosen']);
    Route::post('/dosen/reset-password', [UserController::class, 'resetPasswordDosen']);
    
    Route::post('/admin/forgot-password', [UserController::class, 'sendResetLinkAdmin']);
    Route::post('/admin/reset-password', [UserController::class, 'resetPasswordAdmin']);
});


// Route::post('/register/mahasiswa', [AuthController::class, 'registerUser']);
// Route::post('/login/mahasiswa', [AuthController::class, 'loginUser']);


// Route::post('/mahasiswa/forgot-password', [UserController::class, 'sendResetLinkMahasiswa']);
// Route::post('/mahasiswa/reset-password', [UserController::class, 'resetPasswordMahasiswa']);

// Route::post('/dosen/forgot-password', [UserController::class, 'sendResetLinkDosen']);
// Route::post('/dosen/reset-password', [UserController::class, 'resetPasswordDosen']);

// Route::post('/admin/forgot-password', [UserController::class, 'sendResetLinkAdmin']);
// Route::post('/admin/reset-password', [UserController::class, 'resetPasswordAdmin']);

// ---------- || Route Mahasiswa || ---------- \\

Route::middleware(['auth:sanctum', 'mahasiswa'])->group(function () {

    Route::get('mahasiswa/dashboard', [MahasiswaController::class, 'dashboard']);
    Route::get('mahasiswa/profile', [MahasiswaController::class, 'getProfileMahasiswa']);
    Route::put('mahasiswa/profile/edit', [MahasiswaController::class, 'editProfileMahasiswa']);
    
    
    Route::get('mahasiswa/peminjaman_bhp', [MahasiswaController::class, 'showPeminjamanBhpUser']);
    Route::get('mahasiswa/detail_bhp/{id}', [MahasiswaController::class, 'detailPeminjamanBhp']);
    Route::get('mahasiswa/peminjaman_inventaris', [MahasiswaController::class, 'showPeminjamanInventarisUser']);
    Route::get('mahasiswa/detail_inventaris/{id}', [MahasiswaController::class, 'detailPeminjamanInventaris']);

    // Route::post('/mahasiswa/logout', [AuthController::class, 'logout']);
    // route yg laen
});

    


// ---------- || Route Dosen || ---------- \\

// Route::post('/login/dosen', [AuthDosenController::class, 'loginDosen']);

// Route Global Dosen
Route::middleware(['auth:sanctum', 'dosen'])->group(function () {


    Route::post('/dosen/switch_jabatan', [AuthDosenController::class, 'switchJabatan']);
    Route::get('/dosen/current_jabatan', [AuthDosenController::class, 'getCurrentJabatan']);
    Route::get('/dosen/jabatan', [AuthDosenController::class, 'getUserJabatan']);
    
    Route::get('/dosen/profil', [DosenController::class, 'getProfileDosen']);
    Route::put('/dosen/profil/edit', [DosenController::class, 'editProfileDosen']);


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



Route::get('/admin_pengelola/bhp/export', [BhpController::class, 'exportBhp']);
Route::get('/admin_pengelola/jenis_inventaris/export', [JenisInventarisController::class, 'exportJenisInventaris']);
Route::get('/admin_pengelola/inventaris/export', [InventarisController::class, 'exportInventaris']);
Route::get('/admin_pengelola/peminjaman_bhp/export', [PeminjamanBhpController::class, 'exportPeminjamanBhp']);
Route::get('/admin_pengelola/peminjaman_inventaris/export', [PeminjamanInventarisController::class, 'exportPeminjamanInventaris']);

// ---------- || Route Admin || ---------- \\

// Route::post('/login/admin', [AuthAdminController::class, 'loginAdmin']);


// Route Global Middleware Admin 
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
//  Route::get('/admin/peminjaman-inventaris/send-reminders',  [PeminjamanInventarisController::class, 'manualSendOverdueReminders']);
// Route::post('/logout', [AuthController::class, 'logoutUser']);

 Route::get('/admin/profile',  [AuthAdminController::class, 'getProfileAdmin']);
 Route::put('/admin/profile/edit',  [AuthAdminController::class, 'updateProfileAdmin']);
    // Route Superadmin
    Route::middleware(['auth:sanctum', 'admin', 'admin.role:superadmin'])->group(function () {

        // cuma superadmin yg boleh buat admin klo localan dipindah dlu ke public
        Route::get('/sa/admin', [AuthAdminController::class, 'showAdmin']);
        Route::post('/sa/create_admin', [AuthAdminController::class, 'registerAdmin']);
        Route::get('/sa/admin/{id}', [AuthAdminController::class, 'findAdmin']);
        Route::put('/sa/admin/edit/{id}', [AuthAdminController::class, 'editAdmin']);
        Route::delete('/sa/admin/{id}', [AuthAdminController::class, 'deleteAdmin']);
        



        //Superadmin -> dosenadmin
        Route::get('/superadmin/dashboard', [SuperadminController::class, 'dashboard']);

        Route::post('/superadmin/create_dosen', [SuperadminController::class, 'createDosen']);
        Route::get('/superadmin/dosen', [SuperadminController::class, 'getDosen']);
        Route::get('/superadmin/dosen/{id}', [SuperadminController::class, 'getDosenById']);
        Route::get('/superadmin/dosen/edit/{id}', [SuperadminController::class, 'editDosen']);

        Route::post('/superadmin/dosen/set_status', [SuperadminController::class, 'setStatusDosen']);
        Route::get('/superadmin/jabatan', [SuperadminController::class, 'getJabatan']);
        Route::post('/superadmin/create_jabatan', [SuperadminController::class, 'createJabatan']);
        Route::post('/superadmin/insert_jabatan', [SuperadminController::class, 'insertJabatan']);
        Route::delete('/superadmin/delete_jabatan', [SuperadminController::class, 'deleteJabatan']);


        Route::get('/superadmin/mahasiswa', [SuperadminController::class, 'getMahasiswa']);
        Route::get('/superadmin/mahasiswa/{id}', [SuperadminController::class, 'getMahasiswaById']);
        // Route::get('/superadmin/mahasiswa_konfirmasi', [SuperadminController::class, 'getKonfirmasiMahasiswa']);
        // Route::post('/superadmin/mahasiswa/konfirmasi_mahasiswa', [SuperadminController::class, 'konfirmasiMahasiswa']);
        Route::post('/superadmin/mahasiswa/set_status', [SuperadminController::class, 'setStatusMahasiswa']);
    });

    // Route Admin TA
    Route::middleware(['auth:sanctum', 'admin.role:admin_ta'])->group(function () {
        //route laennya buat admin ta
        Route::get('/admin_ta/dashboard', [AdminTaController::class, 'dashboard']);
    });

    // Route Admin Pengelola (inventaris)
    Route::middleware(['auth:sanctum', 'admin.role:admin_pengelola,superadmin'])->group(function () {
        //route laennya buat admin pengelola
        Route::get('/admin_pengelola/dashboard', [AdminPengelolaController::class, 'dashboard']);

        //BHP
        Route::get('/admin_pengelola/bhp', [BhpController::class, 'showBhp']);
        Route::post('/admin_pengelola/bhp/create', [BhpController::class, 'createBhp']);
        Route::get('/admin_pengelola/bhp/{id}', [BhpController::class, 'findBhp']);
        Route::put('/admin_pengelola/bhp/edit/{id}', [BhpController::class, 'editBhp']);
        Route::delete('/admin_pengelola/bhp/{id}', [BhpController::class, 'deleteBhp']);
        


        // Tipe
        Route::get('/admin_pengelola/tipe', [TipeController::class, 'showTipe']);
        Route::post('/admin_pengelola/tipe/create', [TipeController::class, 'createTipe']);
        Route::get('/admin_pengelola/tipe/{id}', [TipeController::class, 'findTipe']);
        Route::put('/admin_pengelola/tipe/edit/{id}', [TipeController::class, 'editTipe']);
        Route::delete('/admin_pengelola/tipe/{id}', [TipeController::class, 'deleteTipe']);

        // Merk 
        Route::get('/admin_pengelola/merk', [MerkController::class, 'showMerk']);
        Route::post('/admin_pengelola/merk/create', [MerkController::class, 'createMerk']);
        Route::get('/admin_pengelola/merk/{id}', [MerkController::class, 'findMerk']);
        Route::put('/admin_pengelola/merk/edit/{id}', [MerkController::class, 'editMerk']);
        Route::delete('/admin_pengelola/merk/{id}', [MerkController::class, 'deleteMerk']);

        // Jenis Inventaris
        Route::get('/admin_pengelola/jenis_inventaris', [JenisInventarisController::class, 'showJenisInventaris']);
        Route::post('/admin_pengelola/jenis_inventaris/create', [JenisInventarisController::class, 'createJenisInventaris']);
        Route::get('/admin_pengelola/jenis_inventaris/{id}', [JenisInventarisController::class, 'findJenisInventaris']);
        Route::put('/admin_pengelola/jenis_inventaris/edit/{id}', [JenisInventarisController::class, 'editJenisInventaris']);
        Route::delete('/admin_pengelola/jenis_inventaris/{id}', [JenisInventarisController::class, 'deleteJenisInventaris']);

        // Inventaris
        Route::get('/admin_pengelola/inventaris', [InventarisController::class, 'showInventaris']);
        Route::post('/admin_pengelola/inventaris/create', [InventarisController::class, 'createInventaris']);
        Route::get('/admin_pengelola/inventaris/{id}', [InventarisController::class, 'findInventaris']);
        Route::put('/admin_pengelola/inventaris/edit/{id}', [InventarisController::class, 'editInventaris']);
        Route::delete('/admin_pengelola/inventaris/{id}', [InventarisController::class, 'deleteInventaris']);
        

        // Lantai
        Route::get('/admin_pengelola/lantai', [LantaiController::class, 'showLantai']);
        Route::post('/admin_pengelola/lantai/create', [LantaiController::class, 'createLantai']);
        Route::get('/admin_pengelola/lantai/{id}', [LantaiController::class, 'findLantai']);
        Route::put('/admin_pengelola/lantai/edit/{id}', [LantaiController::class, 'editLantai']);
        Route::delete('/admin_pengelola/lantai/{id}', [LantaiController::class, 'deleteLantai']);

        // Gedung 
        Route::get('/admin_pengelola/gedung', [GedungController::class, 'showGedung']);
        Route::post('/admin_pengelola/gedung/create', [GedungController::class, 'createGedung']);
        Route::get('/admin_pengelola/gedung/{id}', [GedungController::class, 'findGedung']);
        Route::put('/admin_pengelola/gedung/edit/{id}', [GedungController::class, 'editGedung']);
        Route::delete('/admin_pengelola/gedung/{id}', [GedungController::class, 'deleteGedung']);

        // Ruang
        Route::get('/admin_pengelola/ruang', [RuangController::class, 'showRuang']);
        Route::post('/admin_pengelola/ruang/create', [RuangController::class, 'createRuang']);
        Route::get('/admin_pengelola/ruang/{id}', [RuangController::class, 'findRuang']);
        Route::put('/admin_pengelola/ruang/edit/{id}', [RuangController::class, 'editRuang']);
        Route::delete('/admin_pengelola/ruang/{id}', [RuangController::class, 'deleteRuang']);

        // get untuk keperluan peminajman 
        Route::get('/admin_pengelola/get_bhp', [AdminPengelolaController::class, 'showBhp']); //status = 1
        // Route::get('/admin_pengelola/bhp/{id}', [AdminPengelolaController::class, 'getBhpById']);
        Route::get('/admin_pengelola/mahasiswa', [AdminPengelolaController::class, 'getMahasiswa']); //status = 1
        Route::get('/admin_pengelola/mahasiswa/{id}', [AdminPengelolaController::class, 'getMahasiswaById']);
        Route::get('/admin_pengelola/dosen', [AdminPengelolaController::class, 'getDosen']); //status = 1
        Route::get('/admin_pengelola/dosen/{id}', [AdminPengelolaController::class, 'getDosenById']);
        Route::get('/admin_pengelola/get_jenis_inventaris', [AdminPengelolaController::class, 'getJenisInventaris']); //kondisi baik klo yang atas seluruh inventaris
        Route::get('/admin_pengelola/get_inventaris', [AdminPengelolaController::class, 'getInventaris']); //kondisi baik klo yang atas seluruh inventaris
        Route::get('/admin_pengelola/get_ruang', [AdminPengelolaController::class, 'getRuang']); 
        Route::get('/admin_pengelola/get_tipe', [AdminPengelolaController::class, 'getTipe']); 
        Route::get('/admin_pengelola/get_merk', [AdminPengelolaController::class, 'getMerk']); 


        // Peminjaman Bhp
        Route::get('/admin_pengelola/peminjaman_bhp', [PeminjamanBhpController::class, 'showPeminjamanBhp']);
        Route::post('/admin_pengelola/peminjaman_bhp/create', [PeminjamanBhpController::class, 'createPeminjamanBhp']);
        Route::get('/admin_pengelola/peminjaman_bhp/{id}', [PeminjamanBhpController::class, 'findPeminjamanBhp']);
        Route::put('/admin_pengelola/peminjaman_bhp/edit/{id}', [PeminjamanBhpController::class, 'editPeminjamanBhp']);
        Route::put('/admin_pengelola/peminjaman_bhp/return/{id}', [PeminjamanBhpController::class, 'returnPeminjamanBhp']);
        Route::delete('/admin_pengelola/peminjaman_bhp/{id}', [PeminjamanBhpController::class, 'deletePeminjamanBhp']);

        // Peminjaman Inventaris
                
        Route::get('/admin_pengelola/peminjaman_inventaris', [PeminjamanInventarisController::class, 'grupPeminjamanInventaris']);
        Route::post('/admin_pengelola/peminjaman_inventaris/create', [PeminjamanInventarisController::class, 'createPeminjamanInventaris']);
        Route::get('/admin_pengelola/peminjaman_inventaris/{id}', [PeminjamanInventarisController::class, 'getDetailPeminjaman']);
        Route::put('/admin_pengelola/peminjaman_inventaris/edit/{id}', [PeminjamanInventarisController::class, 'editPeminjamanInventaris']);
        Route::put('/admin_pengelola/peminjaman_inventaris/pengembalian/{id}', [PeminjamanInventarisController::class, 'pengembalianPeminjamanInventaris']);
        Route::delete('/admin_pengelola/peminjaman_inventaris/{id}', [PeminjamanInventarisController::class, 'deletePeminjamanInventaris']);
        
        
    });
});

// download qrcode
Route::get('/admin_pengelola/bhp/{id}/download_qrcode', [BhpController::class, 'downloadQrCode']);
Route::get('/admin_pengelola/inventaris/{id}/download_qrcode', [InventarisController::class, 'downloadQrCode']);

