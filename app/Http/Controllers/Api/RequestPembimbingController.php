<?php

namespace App\Http\Controllers\Api;

use App\Models\Semester;
use Exception;
use App\Models\Dosen;
use App\Models\Kategori;
use App\Models\TugasAkhir;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\RequestPembimbing;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\RequestPembimbingResource;


class RequestPembimbingController extends Controller
{
    // ========================== FITUR ADMIN ==========================

    public function storeForAdmin(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'tugas_akhir_id' => 'required|integer|exists:tugas_akhir,id',
                'dosen_id' => 'required|integer',
            ]);

            // Cek apakah tugas akhir ada
            $tugasAkhir = TugasAkhir::find($validatedData['tugas_akhir_id']);
            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas akhir tidak ditemukan', 404);
            }

            // Langsung update dosen pembimbing di tugas akhir
            $tugasAkhir->dosen_pembimbing_id = $validatedData['dosen_id'];
            $tugasAkhir->save();

            // Tidak perlu update atau membuat request pembimbing

            // Return tugas akhir dengan relasi dosen pembimbing
            $tugasAkhir->load('dosenPembimbing');

            return ApiResponse::success([
                'tugas_akhir' => [
                    'id' => $tugasAkhir->id,
                    'dosen_pembimbing' => [
                        'id' => $tugasAkhir->dosenPembimbing->id,
                        'name' => $tugasAkhir->dosenPembimbing->name,
                    ]
                ]
            ], 'Dosen pembimbing berhasil ditetapkan', 200);

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }


    public function updateForAdmin(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'dosen_id' => 'required|integer',
            ]);

            // Cari request pembimbing
            $requestPembimbing = RequestPembimbing::with(['tugasAkhir', 'dosen'])->find($id);
            if (!$requestPembimbing) {
                return ApiResponse::error('Request pembimbing tidak ditemukan', 404);
            }

            // Update hanya dosen_pembimbing_id pada TugasAkhir terkait
            $tugasAkhir = $requestPembimbing->tugasAkhir;
            if ($tugasAkhir) {
                $tugasAkhir->dosen_pembimbing_id = $validatedData['dosen_id'];
                $tugasAkhir->save();
            }

            return ApiResponse::success(
                new RequestPembimbingResource($requestPembimbing->load('tugasAkhir', 'dosen')),
                'Dosen pembimbing pada tugas akhir berhasil diupdate'
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR ADMIN ==========================



    // ========================== FITUR MAHASISWA ==========================

    public function storeWithTugasAkhir(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'dosen_id' => 'required|integer',
                'kategori_id' => 'required|integer',
            ]);

            $mahasiswa_id = Auth::id();

            // Cek apakah mahasiswa sudah memiliki TugasAkhir aktif
            $existingTugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa_id)
                ->where('status_aktif', 1)
                ->first();

            if ($existingTugasAkhir) {
                return ApiResponse::error('Mahasiswa sudah memiliki tugas akhir yang aktif', 400);
            }

            // Ambil tahun ajaran default
            $defaultTahunAjaran = TahunAjaran::where('is_default', 1)->first();
            if (!$defaultTahunAjaran) {
                return ApiResponse::error('Tahun ajaran default tidak ditemukan', 400);
            }

            $defaultSemester = Semester::where('is_default', 1)->first();
            if (!$defaultSemester) {
                return ApiResponse::error('Semester default tidak ditemukan', 400);
            }
            // Create TugasAkhir dengan tahun ajaran default
            $tugasAkhir = TugasAkhir::create([
                'mahasiswa_id' => $mahasiswa_id,
                'kategori_id' => $validatedData['kategori_id'],
                'semester_id' => $defaultSemester->id,
                'status_aktif' => 1,
                'tahun_ajaran_id' => $defaultTahunAjaran->id,
            ]);

            // Buat RequestPembimbing
            $requestPembimbing = RequestPembimbing::create([
                'tugas_akhir_id' => $tugasAkhir->id,
                'dosen_id' => $validatedData['dosen_id'],
                'status_request' => 0,
                'tanggal_respon' => null,
            ]);

            return ApiResponse::success(
                new RequestPembimbingResource($requestPembimbing->load('tugasAkhir', 'dosen')),
                'Request pembimbing dan Tugas Akhir berhasil dibuat',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Data request pembimbing gagal ditambahkan: ' . $e->getMessage(), 500);
        }
    }

    public function cekStatusRequestByMahasiswa()
    {
        try {
            $mahasiswaId = Auth::id();

            // Ambil data RequestPembimbing berdasarkan mahasiswa_id melalui relasi TugasAkhir
            $requestPembimbing = RequestPembimbing::whereHas('tugasAkhir', function ($query) use ($mahasiswaId) {
                $query->where('mahasiswa_id', $mahasiswaId);
            })->first();

            // Jika tidak ditemukan, kembalikan null untuk status_request
            if (!$requestPembimbing) {
                return ApiResponse::success([
                    'status_request' => null,
                ], 'Belum ada request pembimbing');
            }

            return ApiResponse::success([
                'status_request' => $requestPembimbing->status_request,
            ], 'Request sudah direspon');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function requestPembimbingForMahasiswa()
    {
        try {
            $mahasiswa = Auth::id();

            $tugasAkhir = TugasAkhir::with([
                'dosenPembimbing:id,name',
                'kategori:id,nama_kategori',
                'requestPembimbing.dosen:id,name,nik',
                'tahunAjaran:id,tahun_ajaran'
            ])
                ->where('mahasiswa_id', $mahasiswa)
                ->where('status_aktif', 1)
                ->select('id', 'status_aktif', 'dosen_pembimbing_id', 'kategori_id', 'tahun_ajaran_id')
                ->first();

            // Ambil semua kategori
            $listKategori = Kategori::select('id', 'nama_kategori as name')->get();

            // Ambil semua dosen
            $listDosen = Dosen::select('id', 'name', 'nik')->get();

            // Ambil tahun ajaran default
            $defaultTahunAjaran = TahunAjaran::where('is_default', 1)
                ->select('id', 'tahun_ajaran')
                ->first();

            $defaultTahunAjaranArr = $defaultTahunAjaran
                ? [
                    'id' => $defaultTahunAjaran->id,
                    'tahun_ajaran' => $defaultTahunAjaran->tahun_ajaran,
                ]
                : null;

            // Jika tidak ada tugas akhir aktif, kembalikan data kosong/null
            if (!$tugasAkhir) {
                return ApiResponse::success([
                    'tugas_akhir' => null,
                    'list_kategori' => $listKategori,
                    'list_dosen' => $listDosen,
                    'default_tahun_ajaran' => $defaultTahunAjaranArr,
                ], 'Belum ada tugas akhir aktif');
            }

            $result = [
                'tugas_akhir' => [
                    'id' => $tugasAkhir->id,
                    'status_active' => $tugasAkhir->status_aktif,
                    'dosen_pembimbing' => $tugasAkhir->dosenPembimbing ? [
                        'id' => $tugasAkhir->dosenPembimbing->id,
                        'nama' => $tugasAkhir->dosenPembimbing->name,
                    ] : null,
                    'kategori' => $tugasAkhir->kategori ? [
                        'id' => $tugasAkhir->kategori->id,
                        'nama' => $tugasAkhir->kategori->nama_kategori,
                    ] : null,
                    'request_pembimbing' => $tugasAkhir->requestPembimbing ? [
                        'id' => $tugasAkhir->requestPembimbing->id,
                        'status_request' => $tugasAkhir->requestPembimbing->status_request,
                        'dosen' => $tugasAkhir->requestPembimbing->dosen ? [
                            'id' => $tugasAkhir->requestPembimbing->dosen->id,
                            'nama' => $tugasAkhir->requestPembimbing->dosen->name,
                            'nik' => $tugasAkhir->requestPembimbing->dosen->nik,
                        ] : null,
                    ] : null,
                    'tahun_ajaran' => $tugasAkhir->tahunAjaran ? [
                        'id' => $tugasAkhir->tahunAjaran->id,
                        'tahun_ajaran' => $tugasAkhir->tahunAjaran->tahun_ajaran,
                    ] : null,
                ],
                'list_kategori' => $listKategori,
                'list_dosen' => $listDosen,
                'default_tahun_ajaran' => $defaultTahunAjaranArr,
            ];

            return ApiResponse::success($result, 'Data permintaan pembimbing berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Gagal mengambil data permintaan pembimbing: ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR MAHASISWA ==========================


    // ========================== FITUR DOSEN ==========================

    public function tabelRequestPembimbingForDosen()
    {
        try {

            $dosenId = Auth::id();

            $requestPembimbing = RequestPembimbing::with([
                'tugasAkhir.mahasiswa:id,name,nim',
                'tugasAkhir.kategori:id,nama_kategori'
            ])
                ->where('status_request', 0)
                ->where('dosen_id', $dosenId)
                ->orderBy('tanggal_request', 'asc')
                ->paginate(10);

            $data = $requestPembimbing->map(function ($item) {
                Carbon::setLocale('id');

                return [
                    'request_pembimbing_id' => $item->id,
                    'tugas_akhir_id' => $item->tugasAkhir->id,
                    'mahasiswa' => [
                        'id' => $item->tugasAkhir->mahasiswa->id ?? null,
                        'name' => $item->tugasAkhir->mahasiswa->name ?? null,
                        'nim' => $item->tugasAkhir->mahasiswa->nim ?? null,
                    ],
                    'kategori' => [
                        'id' => $item->tugasAkhir->kategori->id ?? null,
                        'nama_kategori' => $item->tugasAkhir->kategori->nama_kategori ?? null,
                    ],
                    'tanggal_request' => $item->tanggal_request
                        ? Carbon::parse($item->tanggal_request)->translatedFormat('l, d F Y')
                        : null,
                ];
            });

            $result = [
                'data' => $data,
                'pagination' => [
                    'current_page' => $requestPembimbing->currentPage(),
                    'last_page' => $requestPembimbing->lastPage(),
                    'per_page' => $requestPembimbing->perPage(),
                    'total' => $requestPembimbing->total(),
                ],
            ];
            return ApiResponse::success($result, 'Data request pembimbing berhasil diambil');

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function updateApprovalForDosen(Request $request, $id)
    {
        try {
            $requestPembimbing = RequestPembimbing::with(['tugasAkhir', 'dosen'])->find($id);

            if (!$requestPembimbing) {
                return ApiResponse::error('Request not found', 404);
            }

            // Check if the logged-in user is the dosen assigned to this request
            $dosenId = Auth::user()->id;
            if ($requestPembimbing->dosen_id != $dosenId) {
                return ApiResponse::error('Unauthorized', 403);
            }

            $validatedData = $request->validate([
                'status_request' => 'required|integer',
                'tanggal_respon' => 'sometimes|integer',
            ]);

            $requestPembimbing->update([
                'status_request' => $validatedData['status_request'],
                'tanggal_respon' => Carbon::now(),
            ]);

            // Update dosen_pembimbing_id in TugasAkhir if status_request is 1
            if ($validatedData['status_request'] == 1) {
                $tugasAkhir = TugasAkhir::find($requestPembimbing->tugas_akhir_id);
                if ($tugasAkhir) {
                    $tugasAkhir->dosen_pembimbing_id = $requestPembimbing->dosen_id;
                    $tugasAkhir->save();
                }
            }

            return ApiResponse::success(new RequestPembimbingResource($requestPembimbing->load('tugasAkhir', 'dosen')), 'Request updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR DOSEN ==========================

}