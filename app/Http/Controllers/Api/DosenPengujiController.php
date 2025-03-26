<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\TugasAkhir;
use App\Models\DosenPenguji;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TugasAkhirResource;
use App\Http\Resources\DosenPengujiResource;

class DosenPengujiController extends Controller
{
    public function getReadyTA()
    {
        try {
            // Cek list tugas akhir yang sudah memiliki minimal 9 bimbingan yang sudah diverifikasi (bisa lanjut ke semhas & ta)
            $listTugasAkhir = TugasAkhir::withCount([
                'bimbingan' => function ($query) {
                    $query->where('status_verifikasi', 1);
                }
            ])
                ->having('bimbingan_count', '>=', 9)
                ->paginate(10);

            return ApiResponse::success(TugasAkhirResource::collection($listTugasAkhir));
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'list_dosen_id' => 'required|array|size:3',
                'list_dosen_id.*' => 'required|exists:dosen,id',
                'tugas_akhir_id' => 'required|exists:tugas_akhir,id',
            ], [
                'list_dosen_id.size' => 'Exactly 3 dosen must be provided.',
            ]);

            $tugasAkhir = TugasAkhir::findOrFail($request->tugas_akhir_id);

            // Cek apakah jumlah dosen penguji lebih dari 3 langsung dari database DosenPenguji agar lebih efisien (tidak perlu melakukan query lagi)
            if (DosenPenguji::where('tugas_akhir_id', $request->tugas_akhir_id)->count() >= 3) {
                return ApiResponse::error('Maksimal 3 dosen penguji untuk tugas akhir ini!', 400);
            }

            // Memastikan salah satu dosen yang dipilih adalah Dosen Pembimbing
            if (!in_array($tugasAkhir->dosen_pembimbing_id, $request->list_dosen_id)) {
                return ApiResponse::error('Salah satu dosen penguji harus dari dosen pembimbing', 400);
            }

            // Cek apakah dosen yang dipilih sudah ada sebagai dosen penguji di tugas akhir yang sama
            $cekDosenPenguji = DosenPenguji::where('tugas_akhir_id', $request->tugas_akhir_id)
                ->whereIn('dosen_id', $request->list_dosen_id)
                ->exists();

            if ($cekDosenPenguji) {
                return ApiResponse::error('Dosen yang anda pilih sudah ada di tugas akhir ini', 400);
            }

            $dosenPengujiData = [];
            foreach ($request->list_dosen_id as $dosenId) {
                $dosenPengujiData[] = [
                    'dosen_id' => $dosenId,
                    'tugas_akhir_id' => $request->tugas_akhir_id,
                    'is_dosen_pembimbing' => $dosenId == $tugasAkhir->dosen_pembimbing_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DosenPenguji::insert($dosenPengujiData);

            $dosenPenguji = DosenPenguji::with(['tugasAkhir', 'dosen'])->where('tugas_akhir_id', $request->tugas_akhir_id)->get();

            return ApiResponse::success(DosenPengujiResource::collection($dosenPenguji), 'Dosen penguji berhasil ditambahkan', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Data sesi gagal ditambahkan: ' . $e->getMessage(), 500);
        }
    }
}
