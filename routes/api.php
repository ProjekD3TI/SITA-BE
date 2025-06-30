<?php

use App\Http\Controllers\Admin\AdminPengelola\AdminPengelolaController;
use App\Http\Controllers\Admin\AdminTa\AdminTaController;
use App\Http\Controllers\Admin\Superadmin\SuperadminController;
use App\Http\Controllers\Admin\AdminPengelola\GedungController;
use App\Http\Controllers\Admin\AdminPengelola\LantaiController;
use App\Http\Controllers\Admin\AdminPengelola\RuangController;
use App\Http\Controllers\Admin\AdminPengelola\MahasiswaAdminController;
use App\Http\Controllers\Admin\AdminPengelola\DosenAdminController;
use App\Http\Controllers\Admin\AdminPengelola\GedungLantaiController;
use App\Http\Controllers\Admin\AdminPengelola\BookingController;
use App\Http\Controllers\Admin\AdminPengelola\ClassesController;
use App\Http\Controllers\Admin\AdminPengelola\FixedScheduleController;
use App\Http\Controllers\Admin\AdminPengelola\RfidTagController;
use App\Http\Controllers\Admin\AdminPengelola\SemesterController;
use App\Http\Controllers\RFID\AccessController;
use App\Http\Controllers\Admin\AdminPengelola\SessionController;
use App\Http\Controllers\Auth\AuthAdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AuthDosenController;
use App\Http\Controllers\Dosens\Dosen\DosenController;
use App\Http\Controllers\Dosens\Kalab\KalabController;
use App\Http\Controllers\Dosens\Kaprodi\KaprodiController;
use App\Http\Controllers\Dosens\Petugas\PetugasController;
use App\Http\Controllers\Mahasiswa\MahasiswaController;
use App\Http\Controllers\UserController;
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
Route::get('/ruang', [RuangController::class, 'showRuang']);
Route::get('/access/log', [AccessController::class, 'getLogs']);
Route::post('/access/log', [AccessController::class, 'log']);
Route::post('/access/check', [AccessController::class, 'check']);
Route::get('/booking/request', [BookingController::class, 'getRequestBooking']);


// route sanctum global
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/logout', [UserController::class, 'logoutUser']);
});


Route::post('/register/mahasiswa', [AuthController::class, 'registerUser']);
Route::post('/login/mahasiswa', [AuthController::class, 'loginUser']);


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
    Route::get('/mahasiswa/statistik', [MahasiswaController::class, 'getStatistik']);


    Route::get('/mahasiswa/available-sessions', [BookingController::class, 'getAvailableSessions']);    
    Route::get('/mahaiswa/fixed_schedule', [FixedScheduleController::class, 'getSchedule']);
    Route::get('/mahasiswa/booking', [BookingController::class, 'getUserBooking']);
    Route::get('/mahasiswa/session', [SessionController::class, 'getSession']);
    Route::get('/mahasiswa/ruang', [RuangController::class, 'showRuang']);
    Route::post('/mahasiswa/booking/create', [BookingController::class, 'createBooking']);
    
    // Route::post('/mahasiswa/logout', [AuthController::class, 'logout']);
    // route yg laen
});

    


// ---------- || Route Dosen || ---------- \\

Route::post('/login/dosen', [AuthDosenController::class, 'loginDosen']);

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
        Route::get('/dosen/fixed_schedule', [FixedScheduleController::class, 'getSchedule']);
        Route::get('/dosen/booking', [BookingController::class, 'getUserBooking']);
        Route::get('/dosen/session', [SessionController::class, 'getSession']);
        Route::get('/dosen/ruang', [RuangController::class, 'showRuang']);
        Route::post('/dosen/booking/create', [BookingController::class, 'createBooking']);
        Route::get('/dosen/booking', [BookingController::class, 'getDosenBooking']);
    });

    // Route Kalab
    Route::middleware(['auth:sanctum', 'jabatan.dosen:Kalab'])->group(function () {
        Route::get('/kalab/dashboard', [KalabController::class, 'dashboard']);
        
         //Booking
        Route::get('/kalab/booking', [BookingController::class, 'getRequestBooking']);
        Route::get('/kalab/booking/{id}', [BookingController::class, 'getRequestBookingById']);
        Route::put('/kalab/booking/approve/{id}', [BookingController::class, 'approveBooking']);
        Route::get('/kalab/booking', [BookingController::class, 'getRequestBooking']);
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


// cuma superadmin yg boleh buat admin klo localan dipindah dlu ke public
Route::post('/register/admin', [AuthAdminController::class, 'registerAdmin']);

// ---------- || Route Admin || ---------- \\

Route::post('/login/admin', [AuthAdminController::class, 'loginAdmin']);

// Route Global Middleware Admin 
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Route::post('/logout', [AuthController::class, 'logoutUser']);

    // Route Superadmin
    Route::middleware(['auth:sanctum', 'admin.role:superadmin'])->group(function () {




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
        //route laennya buat admin ta
        Route::get('/admin_ta/dashboard', [AdminTaController::class, 'dashboard']);
    });

    // Route Admin Pengelola (inventaris)
    Route::middleware(['auth:sanctum', 'admin.role:admin_pengelola'])->group(function () {
        //route laennya buat admin pengelola
        Route::get('/admin_pengelola/dashboard', [AdminPengelolaController::class, 'dashboard']);
        Route::get('/admin_pengelola/mahasiswa', [MahasiswaAdminController::class, 'index']);
        Route::get('/admin_pengelola/dosen', [DosenAdminController::class, 'index']);
        Route::get('/admin_pengelola/dosen/{id}', [DosenAdminController::class, 'show']);
        Route::get('/admin_pengelola/mahasiswa/{id}', [MahasiswaAdminController::class, 'show']);
        
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

        //gedunglantai
        Route::get('/gedung-lantai', [GedungLantaiController::class, 'getGedungLantai']);
        Route::post('/gedung-lantai/create', [GedungLantaiController::class, 'createGedungLantai']);
        Route::get('/gedung-lantai/{id}', [GedungLantaiController::class, 'getGedungLantaiById']);
        Route::put('/gedung-lantai/edit/{id}', [GedungLantaiController::class, 'updateGedungLantai']);
        Route::delete('/gedung-lantai/{id}', [GedungLantaiController::class, 'deleteGedungLantai']);

        // Ruang
        Route::get('/admin_pengelola/ruang', [RuangController::class, 'showRuang']);
        Route::post('/admin_pengelola/ruang/create', [RuangController::class, 'createRuang']);
        Route::get('/admin_pengelola/ruang/{id}', [RuangController::class, 'findRuang']);
        Route::put('/admin_pengelola/ruang/edit/{id}', [RuangController::class, 'editRuang']);
        Route::delete('/admin_pengelola/ruang/{id}', [RuangController::class, 'deleteRuang']);

        // Session
        Route::get('/admin_pengelola/session', [SessionController::class, 'getSession']);
        Route::post('/admin_pengelola/session/create', [SessionController::class, 'createSession']);
        Route::get('/admin_pengelola/session/{id}', [SessionController::class, 'getSessionById']);
        Route::put('/admin_pengelola/session/update/{id}', [SessionController::class, 'updateSession']);
        Route::delete('/admin_pengelola/session/{id}', [SessionController::class, 'deleteSession']);

        //Classes
        Route::get('/admin_pengelola/classes', [ClassesController::class, 'getClasses']);
        Route::post('/admin_pengelola/classes/create', [ClassesController::class, 'createClasses']);
        Route::get('/admin_pengelola/classes/{id}', [ClassesController::class, 'getClassesById']);
        Route::put('/admin_pengelola/classes/update/{id}', [ClassesController::class, 'updateClasses']);
        Route::delete('/admin_pengelola/classes/{id}', [ClassesController::class, 'deleteClasses']);

        // Semester
        Route::get('/admin_pengelola/semester', [SemesterController::class, 'getSemester']);
        Route::post('/admin_pengelola/semester/create', [SemesterController::class, 'createSemester']);
        Route::get('/admin_pengelola/semester/{id}', [SemesterController::class, 'getSemesterById']);
        Route::put('/admin_pengelola/semester/update/{id}', [SemesterController::class, 'updateSemester']);
        Route::delete('/admin_pengelola/semester/{id}', [SemesterController::class, 'deleteSemester']);
        
        // Rfid Tag
        Route::get('/admin_pengelola/rfid_tag', [RfidTagController::class, 'getRfid']);
        Route::post('/admin_pengelola/rfid_tag/create', [RfidTagController::class, 'create']);
        Route::get('/admin_pengelola/rfid_tag/{id}', [RfidTagController::class, 'getRfidById']);
        Route::put('/admin_pengelola/rfid_tag/update/{id}', [RfidTagController::class, 'updateRfid']);
        Route::delete('/admin_pengelola/rfid_tag/{id}', [RfidTagController::class, 'deleteRfid']);
        
        // Fixed Schedule
        Route::get('/admin_pengelola/fixed_schedule', [FixedScheduleController::class, 'getSchedule']);
        Route::post('/admin_pengelola/fixed_schedule/create', [FixedScheduleController::class, 'createSchedule']);
        Route::get('/admin_pengelola/fixed_schedule/{id}', [FixedScheduleController::class, 'getScheduleById']);
        Route::put('/admin_pengelola/fixed_schedule/update/{id}', [FixedScheduleController::class, 'updateSchedule']);
        Route::delete('/admin_pengelola/fixed_schedule/{id}', [FixedScheduleController::class, 'deleteSchedule']);

    });
});

