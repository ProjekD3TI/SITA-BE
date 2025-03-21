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

class ProposalController extends Controller
{
    public function index()
    {
        try {
            $proposals = Proposal::all();
            return ApiResponse::success(ProposalResource::collection($proposals));
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'judul_proposal' => 'required|string',
                'file_proposal' => 'required|file|mimes:pdf',
                'file_transkrip' => 'required|file|mimes:pdf',
            ]);

            // Check if the student has an active TugasAkhir
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $request->user()->id)
                                    ->where('status_aktif', 1)
                                    ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Mahasiswa tidak memiliki tugas akhir yang aktif', 400);
            }

            // Create a new proposal record
            $proposal = Proposal::create([
                'judul_proposal' => $validatedData['judul_proposal'],
                'file_proposal' => $request->file('file_proposal')->store('proposals'),
                'file_transkrip' => $request->file('file_transkrip')->store('proposals'),
            ]);



            return ApiResponse::success(new ProposalResource($proposal), 'Proposal berhasil dibuat');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $validatedData = $request->validate([
                'judul_proposal' => 'required|string',
                'file_proposal' => 'nullable|file|mimes:pdf',
                'file_transkrip' => 'nullable|file|mimes:pdf',
            ]);

            $proposal = Proposal::findOrFail($id);

            if ($request->hasFile('file_proposal')) {
                $proposal->file_proposal = $request->file('file_proposal')->store('proposals');
            }

            if ($request->hasFile('file_transkrip')) {
                $proposal->file_transkrip = $request->file('file_transkrip')->store('proposals');
            }

            $proposal->judul_proposal = $validatedData['judul_proposal'];
            $proposal->save();

            return ApiResponse::success(new ProposalResource($proposal), 'Proposal berhasil diubah');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function updateValidationStatus(Request $request, $id)
{
    try {
        // Validate the request
        $validatedData = $request->validate([
            'status_proposal' => 'required|in:1,2',
        ]);

        // Find the proposal by ID
        $proposal = Proposal::findOrFail($id);

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

        return ApiResponse::success(new ProposalResource($proposal), 'Status proposal berhasil diperbarui');
    } catch (Exception $e) {
        return ApiResponse::error($e->getMessage(), 500);
    }
}
}
