<?php

use App\Models\Bimbingan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\SesiController;
use App\Http\Controllers\Api\RuangController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\BerkasController;
use App\Http\Controllers\Api\UjianTAController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\SemesterController;
use App\Http\Controllers\Api\BimbinganController;
use App\Http\Controllers\Api\TugasAkhirController;
use App\Http\Controllers\Auth\AuthAdminController;
use App\Http\Controllers\Auth\AuthDosenController;
use App\Http\Controllers\Api\TahunAjaranController;
use App\Http\Controllers\Api\DosenPengujiController;
use App\Http\Controllers\Api\NilaiUjianTAController;
use App\Http\Controllers\Api\NilaiBimbinganController;
use App\Http\Controllers\Dosens\Dosen\DosenController;
use App\Http\Controllers\Dosens\Kalab\KalabController;
use App\Http\Controllers\Mahasiswa\MahasiswaController;
use App\Http\Controllers\Admin\AdminTa\AdminTaController;
use App\Http\Controllers\Api\RequestPembimbingController;
use App\Http\Controllers\Dosens\Kaprodi\KaprodiController;
use App\Http\Controllers\Dosens\Petugas\PetugasController;
use App\Http\Controllers\Admin\Superadmin\SuperadminController;
use App\Http\Controllers\Admin\AdminPengelola\AdminPengelolaController;
use App\Http\Controllers\StatusFormController;

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

Route::get('/tahun-ajaran/default', [TahunAjaranController::class, 'getDefault']);
Route::get('/all-dosen', [DosenPengujiController::class, 'getAllDosen']);
Route::get('/berkas/{berkas}/download', [BerkasController::class, 'downloadBerkas'])->name('berkas.download'); //buat download/preview berkas proposal/tugas-akhir



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
    // ----------------- || Sistem Informasi Tugas AKhir || ------------------------ \\
    //request pembimbing
    Route::post('/mahasiswa/request-pembimbing', [RequestPembimbingController::class, 'storeWithTugasAkhir']);
    Route::get('/mahasiswa/cek-request-pembimbing', [RequestPembimbingController::class, 'cekStatusRequestByMahasiswa']);

    //proposal
    Route::get('/mahasiswa/proposal', [ProposalController::class, 'proposalForMahasiswa']);

    Route::post('/mahasiswa/proposal/', [ProposalController::class, 'storeForMahasiswa']);
    Route::post('/mahasiswa/proposal/{id}', [ProposalController::class, 'updateForMahasiswa']);

    // Bimbingan
    Route::get('/mahasiswa/bimbingan/request-pembimbing', [RequestPembimbingController::class, 'requestPembimbingForMahasiswa']);

    Route::get('/mahasiswa/bimbingan/list-bimbingan', [BimbinganController::class, 'showBimbinganForMahasiswa']);
    Route::post('/mahasiswa/bimbingan/', [BimbinganController::class, 'storeBimbinganByMahasiswa']);
    Route::put('/mahasiswa/bimbingan/{id}', [BimbinganController::class, 'updateBimbinganByMahasiswa']);
    Route::delete('/mahasiswa/bimbingan/{id}', [BimbinganController::class, 'destroyBimbinganByMahasiswa']);

    // Nilai Bimbingan
    Route::get('/mahasiswa/nilai-bimbingan', [NilaiBimbinganController::class, 'getNilaiBimbinganByMahasiswa']);

    // Ujian Tugas Akhir
    Route::get('/mahasiswa/ujian-ta', [TugasAkhirController::class, 'ujianTAForMahasiswa']);
    Route::get('/mahasiswa/ujian-ta/nilai-ujian', [NilaiUjianTAController::class, 'getNilaiUjianForMahasiswa']);
    Route::get('/mahasiswa/ujian-ta/jadwal-ujian', [UjianTAController::class, 'jadwalUjianForMahasiswa']);

    Route::get('/mahasiswa/berkas/ujian-ta', [BerkasController::class, 'getBerkasTugasAkhirForMahasiswa']);

    Route::post('/mahasiswa/tugas-akhir/daftar', [TugasAkhirController::class, 'storeUjianTAForMahasiswa']);
    Route::post('/mahasiswa/tugas-akhir/{id}/update', [TugasAkhirController::class, 'updateUjianTAForMahasiswa']);
    Route::get('/mahasiswa/tugas-akhir/cek-dosen-pembimbing', [TugasAkhirController::class, 'cekDosenPembimbingByMahasiswa']);
    Route::get('/mahasiswa/tugas-akhir/aktif', [TugasAkhirController::class, 'tugasAkhirAktifByMahasiswa']);
    Route::get('mahasiswa/tugas-akhir/dashboard', [TugasAkhirController::class, 'setDashboardByMahasiswa']);

    // Kategori
    Route::get('/mahasiswa/kategori', [KategoriController::class, 'lihatKategori']);
    // ----------------- || ########################### || ------------------------ \\

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
    // ----------------- || Sistem Informasi Tugas AKhir || ------------------------ \\

    // Sistem Tugas-akhir (Sesi)
    Route::get('/dosen/sesi', [SesiController::class, 'lihatSesiForDosen']);

    // Sistem Tugas-akhir (Fitur Bimbingan ~ List Request Pembimbing untuk Dosen & Update Status Request)
    Route::get('/dosen/request-pembimbing/', [RequestPembimbingController::class, 'tabelRequestPembimbingForDosen']);
    Route::put('/dosen/request-pembimbing/approval/{requestPembimbingId}', [RequestPembimbingController::class, 'updateApprovalByDosen']);

    // Sistem Tugas-akhir (Fitur Bimbingan ~ List Dibimbing & Detail Bimbingan & Update Verifikasi Bimbingan)
    Route::get('/dosen/bimbingan/', [BimbinganController::class, 'tabelListDibimbingForDosen']);
    Route::get('/dosen/bimbingan/{tugasAkhirId}', [BimbinganController::class, 'tabelDetailBimbinganForDosen']);
    Route::put('/dosen/bimbingan/{bimbinganId}', [BimbinganController::class, 'updateVerifikasiBimbinganByDosen']);

    // Sistem TugasAkhir (Nilai Bimbingan)
    Route::get('/dosen/nilai-bimbingan/{tugasAkhirId}', [NilaiBimbinganController::class, 'getNilaiBimbinganByDosen']);
    Route::post('/dosen/nilai-bimbingan/{tugasAkhirId}', [NilaiBimbinganController::class, 'storeNilaiBimbinganByDosen']);
    Route::put('/dosen/nilai-bimbingan/{tugasAkhirId}/update', [NilaiBimbinganController::class, 'updateNilaiBimbinganByDosen']);

    // Sistem Tugas-akhir (Nilai Ujian TA)
    Route::post('/dosen/nilai-ujian-ta/', [NilaiUjianTAController::class, 'storeNilaiByDosenPenguji']);
    Route::put('/dosen/nilai-ujian-ta/{tugasAkhirId}', [NilaiUjianTAController::class, 'updateNilaiByDosenPenguji']);

    // Sistem Tugas-akhir (Fitur Ujian Tugas Akhir ~ List Ujian TA untuk Dosen & Detail Ujian TA)
    Route::get('/dosen/list-ujian-ta', [TugasAkhirController::class, 'tabelUjianTAForDosen']);
    Route::get('/dosen/detail-ujian-ta/{tugasAkhirId}', [TugasAkhirController::class, 'detailUjianForDosen']);

    // Sistem Tugas akhir (Tugas Akhir)
    Route::put('/dosen/status-kelulusan/{tugasAkhirId}', [TugasAkhirController::class, 'updateStatusKelulusanByDosenPembimbing']);

    Route::get('/dosen/tugas-akhir/dashboard', [TugasAkhirController::class, 'setDashboardByDosen']);
    // Kategori
    Route::get('/dosen/kategori', [KategoriController::class, 'lihatKategori']);
    // ----------------- || ############################### || ------------------------ \\



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


        // Fitur Bimbingan (Plotting List Dosen Pembimbing & Detail Plotting Pembimbing & Update Plotting Pembimbing)
        Route::get('/admin_ta/tugas-akhir/plotting', [TugasAkhirController::class, 'tabelPlottingPembimbingForAdmin']);
        Route::get('/admin_ta/tugas-akhir/plotting-pembimbing/show/{tugasAkhirId}', [TugasAkhirController::class, 'plottingPembimbingForAdmin']);
        Route::post('/admin_ta/tugas-akhir/plotting-pembimbing/', [TugasAkhirController::class, 'storePlottingPembimbingByAdmin']);
        Route::put('/admin_ta/tugas-akhir/plotting-pembimbing/{tugasAkhirId}', [TugasAkhirController::class, 'putPlottingPembimbingByAdmin']);

        // Fitur Bimbingan (List Bimbingan & Detail Bimbingan)
        Route::get('/admin_ta/tugas-akhir/list-bimbingan', [TugasAkhirController::class, 'tabelListBimbinganForAdmin']);
        Route::get('/admin_ta/tugas-akhir/detail-bimbingan/{tugasAkhirId}', [TugasAkhirController::class, 'detailBimbinganForAdmin']);

        // Fitur Proposal (List Pengajuan Proposal & Detail Pengajuan Proposal & Validasi Berkas)
        Route::get('/admin_ta/pendaftar-proposal', [ProposalController::class, 'tabelListPengajuanProposalForAdmin']);
        Route::get('/admin_ta/pendaftar-proposal/{proposalId}', [ProposalController::class, 'detailPengajuanProposalForAdmin']);
        Route::put('/admin_ta/pendaftar-proposal/{proposalId}/status', [ProposalController::class, 'updateValidationStatusForAdmin']);

        // Fitur Proposal (List Seminar Proposal & Detail Seminar Proposal & Update Nilai dan Catatan)
        Route::get('/admin_ta/seminar-proposal', [ProposalController::class, 'tabelListSeminarProposalForAdmin']);
        Route::get('/admin_ta/seminar-proposal/{proposalId}', [ProposalController::class, 'detailSeminarProposalForAdmin']);
        Route::put('/admin_ta/seminar-proposal/{proposalId}/nilai-catatan', [ProposalController::class, 'updateNilaidanCatatanForAdmin']);

        // Fitur Ujian TA (List Plotting Penguji & Detail Plotting Penguji)
        Route::get('/admin_ta/plotting-penguji', [TugasAkhirController::class, 'tabelPlottingPengujiForAdmin']);
        Route::get('/admin_ta/plotting-penguji/{tugasAkhirId}', [TugasAkhirController::class, 'plottingPengujiForAdmin']);

        // Fitur Ujian TA (List Pendaftar Ujian & Detail Pendaftar Ujian)
        Route::get('/admin_ta/pendaftar-ujian', [TugasAkhirController::class, 'tabelPendaftarUjianForAdmin']);
        Route::get('/admin_ta/pendaftar-ujian/{tugasAkhirId}', [TugasAkhirController::class, 'showPendaftarUjianForAdmin']);
        Route::put('/admin_ta/pendaftar-ujian/{tugasAkhirId}/status', [TugasAkhirController::class, 'validasiBerkasByAdmin']);

        // Fitur Ujian TA (List Jadwal Ujian & Berita Acara Ujian)
        Route::get('/admin_ta/penjadwalan-ujian', [TugasAkhirController::class, 'tabelPenjadwalanUjianByAdmin']);
        Route::get('/admin_ta/penjadwalan-ujian/{tugasAkhirId}', [TugasAkhirController::class, 'showPenjadwalanUjianByAdmin']);

        // Berita Acara Semhas
        // Route::get('/admin_ta/list-berita-acara', [TugasAkhirController::class, 'tabelBeritaAcaraByAdmin']);

        // Kategori
        Route::post('/admin_ta/tambah-kategori', [KategoriController::class, 'tambahKategoriByAdmin']);
        Route::get('/admin_ta/kategori', [KategoriController::class, 'lihatKategori']);
        Route::put('/admin_ta/edit-kategori/{id}', [KategoriController::class, 'editKategoriByAdmin']);

        // Plotting Pembimbing
        Route::post('/admin_ta/request-pembimbing/', [RequestPembimbingController::class, 'storeForAdmin']);
        Route::put('/admin_ta/request-pembimbing/{id}', [RequestPembimbingController::class, 'updateForAdmin']);



        // Plotting penguji
        Route::get('/admin_ta/dosen-penguji', [DosenPengujiController::class, 'getReadyTAForAdmin']);
        Route::post('/admin_ta/dosen-penguji/', [DosenPengujiController::class, 'storeForAdmin']);
        Route::put('/admin_ta/dosen-penguji/{id}', [DosenPengujiController::class, 'updateForAdmin']);

        // Status Form
        Route::get('/admin_ta/status-form', [StatusFormController::class, 'getStatusFormByAdmin']);
        Route::put('/admin_ta/status-form/{idForm}', [StatusFormController::class, 'updateStatusFormByAdmin']);

        // Route Sesi
        Route::apiResource('/admin_ta/sesi', SesiController::class);

        // User Management
        Route::get('/admin_ta/mahasiswa-ta', [AdminTaController::class, 'getMahasiswa']);
        Route::put('/admin_ta/mahasiswa-ta', [AdminTaController::class, 'setAksesTAMahasiswa']);
        Route::get('/admin_ta/dosen-ta', [AdminTaController::class, 'getDosen']);
        Route::put('/admin_ta/dosen-ta', [AdminTaController::class, 'setAksesTADosen']);

        // Route Ruang
        Route::apiResource('/admin_ta/ruang', RuangController::class);

        // Route Semester
        Route::get('/admin_ta/semester', [SemesterController::class, 'index']);
        Route::get('/admin_ta/semester/{id}', [SemesterController::class, 'show']);
        Route::put('/admin_ta/semester/{id}', [SemesterController::class, 'update']);
        Route::put('/admin_ta/semester/{id}/set-default', [SemesterController::class, 'setDefault']);

        // Route Tahun Ajaran
        Route::apiResource('/admin_ta/tahun-ajaran', TahunAjaranController::class);
        Route::put('admin_ta/tahun-ajaran/{id}/set-default', [TahunAjaranController::class, 'setDefault']);

        // Route Ujian TA
        // Route::get('/admin_ta/ujian-ta', [UjianTAController::class, 'index']);
        // Route::get('/admin_ta/ujian-ta', [UjianTAController::class, 'index']);
        Route::post('/admin_ta/ujian-ta/', [UjianTAController::class, 'store']);
        Route::put('/admin_ta/ujian-ta/{id}', [UjianTAController::class, 'update']);
        // Route::get('/admin_ta/ujian-ta/daftar-ta', [UjianTAController::class, 'getTAReadyForAdmin']);
        // Route::get('/admin_ta/dashboard', [AdminTaController::class, 'setDashboardByAdmin']);
    });

    // Route Admin Pengelola (inventaris)
    Route::middleware(['auth:sanctum', 'admin.role:admin_pengelola'])->group(function () {
        //route laennya buat admin pengelola
        Route::get('/admin_pengelola/dashboard', [AdminPengelolaController::class, 'dashboard']);
    });
});
