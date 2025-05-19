<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\Proposal;
use App\Models\TugasAkhir;
use App\Http\Resources\ProposalResource;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProposalController extends Controller
{
    public function indexForAdmin()
    {
        try {
            $proposals = Proposal::all();
            return ApiResponse::success(ProposalResource::collection($proposals));
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

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

            // Simpan file proposal dan transkrip
            $originalName = $request->file('file_proposal')->getClientOriginalName();
            $filePath = $request->file('file_proposal')->storeAs("proposal/{$proposal->id}", $originalName);

            $proposal->berkas()->create([
                'nama_berkas' => 'file_proposal',
                'file_path' => $filePath,
                'status_valid' => 0,
            ]);
            $originalName = $request->file('file_transkrip')->getClientOriginalName();
            $filePath = $request->file('file_transkrip')->storeAs("proposal/{$proposal->id}", $originalName);

            $proposal->berkas()->create([
                'nama_berkas' => 'file_transkrip',
                'file_path' => $filePath,
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
                $berkasProposal = $proposal->berkas()->where('nama_berkas', 'file_proposal')->first();
                if ($berkasProposal) {
                    $originalName = $request->file('file_proposal')->getClientOriginalName();
                    $berkasProposal->file_path = $request->file('file_proposal')->storeAs("proposal/{$proposal->id}", $originalName);
                    $berkasProposal->status_valid = 0; // Saat update, set status_valid ke 0
                    $berkasProposal->save();
                }
            }

            // Update berkas transkrip jika ada file baru
            if ($request->hasFile('file_transkrip')) {
                $berkasTranskrip = $proposal->berkas()->where('nama_berkas', 'file_transkrip')->first();
                if ($berkasTranskrip) {
                    $originalName = $request->file('file_transkrip')->getClientOriginalName();
                    $berkasTranskrip->file_path = $request->file('file_transkrip')->storeAs("proposal/{$proposal->id}", $originalName);
                    $berkasTranskrip->status_valid = 0; // Saat update, set status_valid ke 0
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

            // Check if the student has an active TugasAkhir
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $request->user()->id)
                ->where('status_aktif', 1)
                ->first();

            // If status_proposal is 1 (accepted), update the TugasAkhir with the proposal_id
            if ($validatedData['status_proposal'] == 1) {
                $tugasAkhir->proposal_id = $proposal->id;
                $tugasAkhir->save();
            }

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
}
