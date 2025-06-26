<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Berkas;
use App\Models\Proposal;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BerkasResource;
use Illuminate\Support\Facades\Storage;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BerkasController extends Controller
{
    public function downloadBerkas($berkasId)
    {
        $user = Auth::guard('admin')->user()
            ?? Auth::guard('dosen')->user()
            ?? Auth::guard('mahasiswa')->user()
            ?? auth()->user();

        $berkas = Berkas::findOrFail($berkasId);
        $parent = $berkas->berkasable; // Proposal atau TugasAkhir

        // ADMIN: boleh akses semua
        if ($user instanceof Admin) {
            // lanjut ke download
        }
        // MAHASISWA: hanya boleh akses file miliknya
        elseif ($user instanceof User) {
            $mahasiswaId = $user->id;
            if ($parent instanceof Proposal) {
                $tugasAkhir = $parent->tugasAkhir;
                if (!$tugasAkhir || $tugasAkhir->mahasiswa_id != $mahasiswaId) {
                    return response()->json(['message' => 'Tidak diizinkan (Berkas bukan milik anda)'], 403);
                }
            } elseif ($parent instanceof TugasAkhir) {
                if ($parent->mahasiswa_id != $mahasiswaId) {
                    return response()->json(['message' => 'Tidak diizinkan (Berkas bukan milik anda)'], 403);
                }
            } else {
                return response()->json(['message' => 'Tidak diizinkan'], 403);
            }
        }
        // DOSEN: hanya boleh akses file TA yang dibimbing/diuji
        elseif ($user instanceof Dosen) {
            $dosenId = $user->id;
            $tugasAkhir = null;
            if ($parent instanceof Proposal) {
                $tugasAkhir = $parent->tugasAkhir;
            } elseif ($parent instanceof TugasAkhir) {
                $tugasAkhir = $parent;
            }
            $isPembimbing = $tugasAkhir->dosen_pembimbing_id == $dosenId;
            $isPenguji = $tugasAkhir->dosenPenguji()->where('dosen_id', $dosenId)->exists();
            if (!$isPembimbing && !$isPenguji) {
                return response()->json(['message' => 'Tidak diizinkan (Dosen bukan pembimbing/penguji)'], 403);
            }
        } else {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        // File path dan download
        $filePath = ltrim($berkas->file_path, '/');
        if (!Storage::exists($filePath)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }
        return Storage::download($filePath, basename($filePath));
    }

    public function getBerkasPorposalForAdmin()
    {

    }

    public function getBerkasProposalForMhs()
    {

    }

    public function getBerkasTugasAkhirForMahasiswa($id)
    {
        try {
            $mahasiswaId = auth()->id();

            // Ambil Tugas Akhir berdasarkan ID yang diberikan
            // dan pastikan itu milik mahasiswa yang sedang login.
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswaId)
                ->findOrFail($id);

            // Daftar berkas yang diharapkan ada untuk setiap tugas akhir
            $expectedBerkas = [
                "Naskah Tugas Akhir",
                "Transkrip Nilai Mahasiswa",
                "Scan KRS Semester Akhir",
                "Bukti Persetujuan Pembimbing",
                "Bukti Sertifikat Kompetensi",
                "Sertifikat TOEIC",
                "Berita Acara Seminar Hasil",
                "Surat Perpanjangan Masa Studi"
            ];

            // Ambil semua berkas yang sudah ada di database untuk tugas akhir ini
            $berkasDB = $tugasAkhir->berkas()->get();

            $result = [];
            // Loop melalui daftar berkas yang diharapkan
            foreach ($expectedBerkas as $nama) {
                // Cari berkas yang sesuai dari yang sudah ada di DB
                $berkas = $berkasDB->firstWhere('nama_berkas', $nama);

                // Bangun array respons, pastikan ada entri untuk setiap jenis berkas
                $result[] = [
                    'id' => $berkas->id ?? null,
                    'nama_berkas' => $nama, // Selalu gunakan nama dari expected list
                    'file_path' => $berkas->file_path ?? null,
                    'status_valid' => $berkas->status_valid ?? null,
                    // Buat URL download hanya jika berkas ada
                    'download_url' => $berkas ? route('berkas.download', ['berkas' => $berkas->id]) : null,
                ];
            }

            return ApiResponse::success([
                'berkas' => $result,
                'catatan_perbaikan' => $tugasAkhir->catatan_perbaikan,
            ], 'Daftar berkas tugas akhir berhasil diambil');

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Tugas Akhir tidak ditemukan atau Anda tidak memiliki akses.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengambil berkas tugas akhir: ' . $e->getMessage(), 500);
        }
    }
}