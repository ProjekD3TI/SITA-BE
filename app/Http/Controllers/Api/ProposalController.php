<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\Proposal;
use App\Models\TugasAkhir;
use App\Http\Resources\ProposalResource;
use App\Models\StatusForm;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProposalController extends Controller
{
    // Fitur Proposal Admin TA

    public function tabelPengajuanProposalByAdmin()
    {
        try {
            $listProposal = Proposal::with(['tugasAkhir', 'tugasAkhir.mahasiswa', 'berkas'])
                ->get();

            $statusForm = StatusForm::where('nama_form', 'proposal')
                ->select('id', 'is_open')
                ->first();

            $data = $listProposal->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nim' => $item->tugasAkhir && $item->tugasAkhir->mahasiswa ? $item->tugasAkhir->mahasiswa->nim : null,
                    'mahasiswa' => $item->tugasAkhir && $item->tugasAkhir->mahasiswa ? $item->tugasAkhir->mahasiswa->name : null,
                    'judul_proposal' => $item->judul_proposal ?? null,
                    'status_proposal' => $item->status_proposal ?? null,
                    'tanggal_pengajuan' => $item->created_at ? $item->created_at->format('d M Y') : null,
                    'berkas' => $item->berkas ?? null,
                ];
            });
            return ApiResponse::success([
                'status_form' => $statusForm,
                'list_proposal' => $data
            ], 'Data list proposal berhasil diambil');

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // Fitur Proposal Admin TA

    public function storeForMhs(Request $request)
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

    public function updateForMhs(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'judul_proposal' => 'required|string',
                'file_proposal' => 'nullable|file|mimes:pdf',
                'file_transkrip' => 'nullable|file|mimes:pdf',
            ]);

            $proposal = Proposal::with(['tugasAkhir'])->findOrFail($id);

            $proposal->judul_proposal = $validatedData['judul_proposal'];
            $proposal->save();

            // Update berkas proposal jika ada file baru
            if ($request->hasFile('file_proposal')) {
                $berkasProposal = $proposal->berkas()->where('nama_berkas', 'Naskah Proposal Tugas Akhir')->first();
                if ($berkasProposal) {
                    $originalName = $request->file('file_proposal')->getClientOriginalName();
                    $filePath = $request->file('file_proposal')->storeAs("private/proposal/{$proposal->id}", $originalName);
                    $berkasProposal->file_path = "proposal/{$proposal->id}/{$originalName}";
                    $berkasProposal->status_valid = 0;
                    $berkasProposal->save();
                }
            }

            if ($request->hasFile('file_transkrip')) {
                $berkasTranskrip = $proposal->berkas()->where('nama_berkas', 'Transkrip Nilai Mahasiswa')->first();
                if ($berkasTranskrip) {
                    $originalName = $request->file('file_transkrip')->getClientOriginalName();
                    $filePath = $request->file('file_transkrip')->storeAs("private/proposal/{$proposal->id}", $originalName);
                    $berkasTranskrip->file_path = "proposal/{$proposal->id}/{$originalName}";
                    $berkasTranskrip->status_valid = 0;
                    $berkasTranskrip->save();
                }
            }

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
            // Validate the request
            $validatedData = $request->validate([
                'status_proposal' => 'required|in:1,2',
            ]);

            // Find the proposal by ID with eager loading
            $proposal = Proposal::with(['tugasAkhir'])->findOrFail($id);

            // Update the status_proposal
            $proposal->status_proposal = $validatedData['status_proposal'];
            $proposal->save();

            return ApiResponse::success(new ProposalResource($proposal->load('tugasAkhir')), 'Status proposal berhasil diperbarui');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    public function updateScoreandNotesForAdmin(Request $request, $id)
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


    public function proposalForMhs(Request $request)
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
