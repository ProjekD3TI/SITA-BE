<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProposalTA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProposalTAController extends Controller
{
    public function index()
    {

        $mahasiswaId = auth()->user()->id;
        // Ambil semua proposal TA yang dimiliki oleh mahasiswa yang sedang login
        $proposals = ProposalTA::where('mahasiswa_id', $mahasiswaId)->get();

        return response()->json([
            'status' => true,
            'message' => 'List Data Proposal TA berhasil dimuat',
            'data' => $proposals
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul_proposal' => 'required|string|max:255',
            'file_proposal' => 'required|string|max:255',
            'status_proposal' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $proposal = ProposalTA::create([
            'mahasiswa_id' => auth()->user()->id,
            'judul_proposal' => $request->judul_proposal,
            'file_proposal' => $request->file_proposal,
        ]);
        return response()->json($proposal, 201);
    }

    public function show($id)
    {
        $proposal = ProposalTA::find($id);
        if (is_null($proposal)) {
            return response()->json(['message' => 'Proposal not found'], 404);
        }
        return response()->json($proposal);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'judul_proposal' => 'required|string|max:255',
            'file_proposal' => 'required|string|max:255',
            'status_proposal' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $proposal = ProposalTA::find($id);
        if (is_null($proposal)) {
            return response()->json(['message' => 'Proposal not found'], 404);
        }

        if ($proposal->mahasiswa_id !== auth()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $proposal->update([
            'judul_proposal' => $request->judul_proposal,
            'file_proposal' => $request->file_proposal,
            'status_proposal' => $request->status_proposal,
        ]);
        return response()->json($proposal);
    }

    public function destroy($id)
    {
        $proposal = ProposalTA::find($id);
        if (is_null($proposal)) {
            return response()->json(['message' => 'Proposal not found'], 404);
        }

        $proposal->delete();
        return response()->json(['message' => 'Proposal deleted successfully']);
    }
    public function getProposalsByStatus()
    {
        // Ambil semua proposal TA yang memiliki status_proposal bernilai 1
        $proposals = ProposalTA::where('status_proposal', 1)->get();

        return response()->json([
            'status' => true,
            'message' => 'List Data Proposal TA dengan status 1 berhasil dimuat',
            'data' => $proposals
        ]);
    }

    public function getProposalsforAdmin()
    {
        $proposals = ProposalTA::all();
        return response()->json([
            'status' => true,
            'message' => 'List Data Proposal TA berhasil dimuat',
            'data' => $proposals
        ]);
    }

    public function updateProposalStatusByAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status_proposal' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $status = $request->status_proposal;
        $proposals = ProposalTA::where('status_proposal', $status)->get();

        return response()->json([
            'status' => true,
            'message' => 'List Data Proposal TA dengan status ' . $status . ' berhasil dimuat',
            'data' => $proposals
        ]);
    }
}
