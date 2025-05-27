<?php

namespace App\Http\Controllers\Api;

use App\Models\Berkas;
use App\Models\Proposal;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\BerkasResource;
use Illuminate\Support\Facades\Storage;
use Dotenv\Exception\ValidationException;

class BerkasController extends Controller
{
    public function downloadBerkas($berkasId)
{
    $berkas = Berkas::findOrFail($berkasId);

    // Pastikan path tanpa slash depan
    $filePath = ltrim($berkas->file_path, '/');

    if (!Storage::exists($filePath)) {
        return response()->json(['message' => 'File tidak ditemukan'], 404);
    }

    return Storage::download($filePath, basename($filePath));
}

    public function fetchBerkasProposalForMhs()
    {

    }

    public function getBerkasTugasAkhirForMhs()
{
    try {
        $mahasiswaId = auth()->id();

        $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswaId)
            ->where('status_aktif', 1)
            ->first();

        if (!$tugasAkhir) {
            return ApiResponse::success([], 'Belum ada Tugas Akhir aktif');
        }

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

        $berkasDB = $tugasAkhir->berkas()->get();

        $result = [];
        foreach ($expectedBerkas as $nama) {
            $berkas = $berkasDB->firstWhere('nama_berkas', $nama);
            $result[] = [
                'id' => $berkas->id ?? null,
                'nama_berkas' => $berkas->nama_berkas ?? null,
                'file_path' => $berkas->file_path ?? null,
                'status_valid' => $berkas->status_valid ?? null,
                'download_url' => $berkas ? route('berkas.download', ['berkas' => $berkas->id]) : null,
            ];
        }

        return ApiResponse::success(['berkas' => $result], 'Daftar berkas tugas akhir berhasil diambil');
    } catch (\Exception $e) {
        return ApiResponse::error('Gagal mengambil berkas tugas akhir: ' . $e->getMessage(), 500);
    }
}
}
