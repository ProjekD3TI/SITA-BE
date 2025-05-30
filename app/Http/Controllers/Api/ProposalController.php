<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Kategori;
use App\Models\Proposal;
use App\Models\StatusForm;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProposalResource;
use Illuminate\Validation\ValidationException;

class ProposalController extends Controller
{
    // Fitur Proposal Admin TA

    public function tabelProposalByAdmin()
    {
        try {
            $listProposal = Proposal::with(['tugasAkhir', 'tugasAkhir.mahasiswa', 'berkas'])
                ->get();

            $statusForm = StatusForm::where('nama_form', 'proposal')
                ->select('id', 'is_open')
                ->first();

            // Ambil semua kategori
            $listKategori = Kategori::select('id', 'nama_kategori')->get();

            $data = $listProposal->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nim' => $item->tugasAkhir && $item->tugasAkhir->mahasiswa ? $item->tugasAkhir->mahasiswa->nim : null,
                    'mahasiswa' => $item->tugasAkhir && $item->tugasAkhir->mahasiswa ? $item->tugasAkhir->mahasiswa->name : null,
                    'judul_proposal' => $item->judul_proposal ?? null,
                    'kategori_ta' => $item->tugasAkhir && $item->tugasAkhir->kategori ? [
                        'id' => $item->tugasAkhir->kategori->id,
                        'nama_kategori' => $item->tugasAkhir->kategori->nama_kategori,
                    ] : null,
                    'nilai_proposal' => $item->tugasAkhir ? $item->tugasAkhir->nilai_proposal : null,
                    'status_proposal' => $item->status_proposal ?? null,
                    'tanggal_pengajuan' => $item->created_at ? $item->created_at->format('d M Y') : null,
                    'berkas' => $item->berkas ? $item->berkas->map(function ($berkas) {
                        return [
                            'id' => $berkas->id,
                            'nama_berkas' => $berkas->nama_berkas,
                            'file_path' => $berkas->file_path,
                            'download_url' => route('berkas.download', ['berkas' => $berkas->id]),
                            'status_valid' => $berkas->status_valid,
                        ];
                    })->values() : [],
                ];
            });

            return ApiResponse::success([
                'status_form' => $statusForm,
                'list_proposal' => $data,
                'list_kategori' => $listKategori,
            ], 'Data list proposal & kategori berhasil diambil');

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function showProposalByAdmin($id)
    {
        try {
            $proposal = Proposal::with(['tugasAkhir.mahasiswa', 'berkas'])->find($id);

            if (!$proposal) {
                return ApiResponse::error('Proposal tidak ditemukan untuk ID tersebut', 404);
            }

            $item = [
                'id' => $proposal->id,
                'tugas_akhir_id' => $proposal->tugasAkhir ? $proposal->tugasAkhir->id : null,
                'nim' => $proposal->tugasAkhir && $proposal->tugasAkhir->mahasiswa ? $proposal->tugasAkhir->mahasiswa->nim : null,
                'mahasiswa' => $proposal->tugasAkhir && $proposal->tugasAkhir->mahasiswa ? $proposal->tugasAkhir->mahasiswa->name : null,
                'dosen_pembimbing' => $proposal->tugasAkhir && $proposal->tugasAkhir->dosenPembimbing ? [
                    'id' => $proposal->tugasAkhir->dosenPembimbing->id,
                    'nama' => $proposal->tugasAkhir->dosenPembimbing->name,
                ] : null,
                'judul_proposal' => $proposal->judul_proposal ?? null,
                'kategori_ta' => $proposal->tugasAkhir && $proposal->tugasAkhir->kategori ? [
                    'id' => $proposal->tugasAkhir->kategori->id,
                    'nama_kategori' => $proposal->tugasAkhir->kategori->nama_kategori,
                ] : null,
                'tahun_ajaran' => $proposal->tugasAkhir && $proposal->tugasAkhir->tahunAjaran ? [
                    'id' => $proposal->tugasAkhir->tahunAjaran->id,
                    'tahun_ajaran' => $proposal->tugasAkhir->tahunAjaran->tahun_ajaran,
                ] : null,
                'nilai_proposal' => $proposal->tugasAkhir ? $proposal->tugasAkhir->nilai_proposal : null,
                'catatan_proposal' => $proposal->tugasAkhir ? $proposal->tugasAkhir->catatan_proposal : null,
                'status_proposal' => $proposal->status_proposal ?? null,
                'tanggal_pengajuan' => $proposal->created_at ? $proposal->created_at->format('d M Y') : null,
                'berkas' => $proposal->berkas ? $proposal->berkas->map(function ($berkas) {
                    return [
                        'id' => $berkas->id,
                        'nama_berkas' => $berkas->nama_berkas,
                        'file_path' => $berkas->file_path,
                        'download_url' => route('berkas.download', ['berkas' => $berkas->id]),
                        'status_valid' => $berkas->status_valid,
                    ];
                })->values() : [],
            ];

            return ApiResponse::success($item, 'Data proposal berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // Fitur Proposal Admin TA

    public function storeForMahasiswa(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'judul_proposal' => 'required|string',
                'file_proposal' => 'required|file|mimes:pdf',
                'file_transkrip' => 'required|file|mimes:pdf',
            ]);

            // Cek apakah mahasiswa memiliki tugas akhir yang aktif
            $tugasAkhir = TugasAkhir::with(['proposal'])->where('mahasiswa_id', $request->user()->id)
                ->where('status_aktif', 1)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Mahasiswa tidak memiliki tugas akhir yang aktif', 400);
            }

            // Cek apakah tugas akhir sudah memiliki proposal
            if ($tugasAkhir->proposal_id) {
                return ApiResponse::error('Tugas Akhir yang aktif sudah memiliki proposal', 400);
            }

            // Buat proposal baru
            $proposal = Proposal::create([
                'judul_proposal' => $validatedData['judul_proposal'],
            ]);

            $tugasAkhir->proposal_id = $proposal->id;
            $tugasAkhir->save();

            // Simpan file proposal ke private
            $originalName = $request->file('file_proposal')->getClientOriginalName();
            $filePath = $request->file('file_proposal')->storeAs("private/proposal/{$proposal->id}", $originalName);
            $privatePath = "/private/proposal/{$proposal->id}/{$originalName}";

            $proposal->berkas()->create([
                'nama_berkas' => 'Naskah Proposal Tugas Akhir',
                'file_path' => $privatePath,
                'status_valid' => 0,
            ]);

            // Simpan file transkrip ke private
            $originalName = $request->file('file_transkrip')->getClientOriginalName();
            $filePath = $request->file('file_transkrip')->storeAs("private/proposal/{$proposal->id}", $originalName);
            $privatePath = "/private/proposal/{$proposal->id}/{$originalName}";

            $proposal->berkas()->create([
                'nama_berkas' => 'Transkrip Nilai Mahasiswa',
                'file_path' => $privatePath,
                'status_valid' => 0,
            ]);

            return ApiResponse::success(new ProposalResource($proposal->load('tugasAkhir', 'berkas')), 'Proposal berhasil dibuat');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    public function updateForMahasiswa(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'judul_proposal' => 'required|string',
                'file_proposal' => 'nullable|file|mimes:pdf',
                'file_transkrip' => 'nullable|file|mimes:pdf',
            ]);

            $proposal = Proposal::with(['tugasAkhir'])->findOrFail($id);

            $proposal->judul_proposal = $validatedData['judul_proposal'];
            $isUpload = false;

            // Update berkas proposal jika ada file baru
            if ($request->hasFile('file_proposal')) {
                $berkasProposal = $proposal->berkas()->where('nama_berkas', 'Naskah Proposal Tugas Akhir')->first();
                if ($berkasProposal) {
                    $originalName = $request->file('file_proposal')->getClientOriginalName();
                    $filePath = $request->file('file_proposal')->storeAs("private/proposal/{$proposal->id}", $originalName);
                    $privatePath = "/private/proposal/{$proposal->id}/{$originalName}";
                    $berkasProposal->file_path = $privatePath;
                    $berkasProposal->status_valid = 0;
                    $berkasProposal->save();
                    $isUpload = true;
                }
            }

            if ($request->hasFile('file_transkrip')) {
                $berkasTranskrip = $proposal->berkas()->where('nama_berkas', 'Transkrip Nilai Mahasiswa')->first();
                if ($berkasTranskrip) {
                    $originalName = $request->file('file_transkrip')->getClientOriginalName();
                    $filePath = $request->file('file_transkrip')->storeAs("private/proposal/{$proposal->id}", $originalName);
                    $privatePath = "/private/proposal/{$proposal->id}/{$originalName}";
                    $berkasTranskrip->file_path = $privatePath;
                    $berkasTranskrip->status_valid = 0;
                    $berkasTranskrip->save();
                    $isUpload = true;
                }
            }

                    // Jika ada upload ulang file, set status_proposal ke 0 (menunggu validasi)
            if ($isUpload) {
                $proposal->status_proposal = 0;
            }

            $proposal->save();

            return ApiResponse::success(new ProposalResource($proposal->load('tugasAkhir', 'berkas')), 'Proposal berhasil diubah');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    public function updateValidationStatusForAdmin(Request $request, $id)
    {
        try {
            // Validasi: array status_valid_berkas harus ada dan berisi 2 item (hanya 1 atau 2)
            $validatedData = $request->validate([
                'status_valid_berkas' => 'required|array|size:2',
                'status_valid_berkas.*' => 'required|in:1,2',
            ]);

            $proposal = Proposal::with(['berkas'])->findOrFail($id);

            // Update status_valid pada masing-masing berkas
            $berkasList = $proposal->berkas;
            foreach ($berkasList as $i => $berkas) {
                // urutan status_valid_berkas harus sesuai urutan berkas di FE
                $berkas->status_valid = $validatedData['status_valid_berkas'][$i];
                $berkas->save();
            }

            // Cek status proposal berdasarkan status_valid semua berkas
            $statusList = $validatedData['status_valid_berkas'];
            if (in_array(2, $statusList)) {
                $proposal->status_proposal = 2; // Ditolak
            } elseif (count(array_unique($statusList)) === 1 && $statusList[0] == 1) {
                $proposal->status_proposal = 1; // Diterima
            }
            // Tidak boleh ada status 0, karena sudah divalidasi di atas

            $proposal->save();

            return ApiResponse::success(new ProposalResource($proposal->load('tugasAkhir', 'berkas')), 'Status proposal & berkas berhasil diperbarui');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }
    public function updateNilaidanCatatanForAdmin(Request $request, $id)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'nilai_proposal' => 'required|numeric|min:0|max:100', // Ensure the score is between 0 and 100
                'catatan_proposal' => 'required|string', // Notes are required
            ]);

            // Temukan porposal berdasarkan ID dengan eager loading
            $proposal = Proposal::with(['tugasAkhir'])->findOrFail($id);

            // Pastikan Proposal memiliki TugasAkhir yang terkait
            if (!$proposal->tugasAkhir) {
                return ApiResponse::error('Proposal ini tidak terhubung dengan Tugas Akhir manapun', 404);
            }

            // Update the TugasAkhir record
            $tugasAkhir = $proposal->tugasAkhir;
            $tugasAkhir->nilai_proposal = $validatedData['nilai_proposal'];
            $tugasAkhir->catatan_proposal = $validatedData['catatan_proposal'];
            $tugasAkhir->save();

            // Return the updated TugasAkhir using a resource
            return ApiResponse::success(new ProposalResource($proposal->load('tugasAkhir')), 'Proposal score and notes updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi Gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }


    public function proposalForMahasiswa(Request $request)
    {
        try {
            $mahasiswaId = $request->user()->id;

            // Ambil tugas akhir aktif beserta relasi yang diperlukan
            $tugasAkhir = TugasAkhir::with([
                'dosenPembimbing:id,name',
                'kategori:id,nama_kategori',
                'tahunAjaran:id,tahun_ajaran',
                'proposal.berkas'
            ])
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1)
                ->first();

            if (!$tugasAkhir) {
                // Kembalikan data kosong/null agar FE bisa handle
                return ApiResponse::success([
                    'tugas_akhir' => null,
                    'proposal' => null,
                ], 'Belum ada tugas akhir aktif');
            }

            $proposal = $tugasAkhir->proposal;

            $result = [
                'tugas_akhir' => [
                    'id' => $tugasAkhir->id,
                    'dosen_pembimbing' => $tugasAkhir->dosenPembimbing ? [
                        'id' => $tugasAkhir->dosenPembimbing->id,
                        'nama' => $tugasAkhir->dosenPembimbing->name,
                    ] : null,
                    'kategori_ta' => $tugasAkhir->kategori ? [
                        'id' => $tugasAkhir->kategori->id,
                        'nama' => $tugasAkhir->kategori->nama_kategori,
                    ] : null,
                    'tahun_ajaran' => $tugasAkhir->tahunAjaran ? [
                        'id' => $tugasAkhir->tahunAjaran->id,
                        'nama' => $tugasAkhir->tahunAjaran->tahun_ajaran,
                    ] : null,
                    'proposal_id' => $tugasAkhir->proposal_id,
                    'nilai_proposal' => $tugasAkhir->nilai_proposal,
                    'catatan_proposal' => $tugasAkhir->catatan_proposal,
                ],
                'proposal' => $proposal ? [
                    'id' => $proposal->id,
                    'judul_proposal' => $proposal->judul_proposal,
                    'status_valid' => $proposal->status_proposal,
                    'berkas' => $proposal->berkas->map(function ($berkas) {
                        return [
                            'id' => $berkas->id,
                            'nama_berkas' => $berkas->nama_berkas,
                            'file_path' => $berkas->file_path,
                            'download_url' => route('berkas.download', ['berkas' => $berkas->id]),
                            'status_valid' => $berkas->status_valid,
                        ];
                    })->values(),
                ] : null,
            ];

            return ApiResponse::success($result, 'Data proposal berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengambil data proposal: ' . $e->getMessage(), 500);
        }
    }
}