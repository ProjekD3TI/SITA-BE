<?php

namespace App\Http\Controllers\Api;

use App\Models\ProposalTA;
use Illuminate\Http\Request;
use App\Models\PenilaianProposalTA;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PenilaianProposalTAController extends Controller
{
    public function index()
    {
        $dosenId = auth()->user()->id;

        // Ambil semua penilaian yang memiliki dosen_id sesuai dengan dosen yang sedang login
        $penilaian = PenilaianProposalTA::where('dosen_id', $dosenId)->get();

        return response()->json([
            'status' => true,
            'message' => 'List Data penilaian proposal TA berhasil dimuat',
            'data' => $penilaian
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'proposal_ta_id' => 'required|exists:proposal_ta,id',
            'nilai' => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Cek jumlah penilaian untuk proposal_ta_id
        $penilaianCount = PenilaianProposalTA::where('proposal_ta_id', $request->proposal_ta_id)->count();
        if ($penilaianCount >= 3) {
            return response()->json(['message' => 'Proposal TA ini sudah dinilai 3 Dosen'], 400);
        }

        // Cek apakah dosen sudah menilai proposal ini
        $existingPenilaian = PenilaianProposalTA::where('proposal_ta_id', $request->proposal_ta_id)
        ->where('dosen_id', auth()->user()->id)
        ->first();

        if ($existingPenilaian) {
            return response()->json(['message' => 'Anda sudah menilai proposal TA ini'], 400);
        }

        $penilaian = PenilaianProposalTA::create([
            'proposal_ta_id' => $request->proposal_ta_id,
            'dosen_id' => auth()->user()->id,
            'nilai' => $request->nilai,
        ]);
        return response()->json($penilaian, 201);
    }

    public function show($id)
    {
        $penilaian = PenilaianProposalTA::find($id);
        if (is_null($penilaian)) {
            return response()->json(['message' => 'Penilaian not found'], 404);
        }
        return response()->json($penilaian);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'proposal_ta_id' => 'required|exists:proposal_ta,id',
            'nilai' => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $penilaian = PenilaianProposalTA::find($id);
        if (is_null($penilaian)) {
            return response()->json(['message' => 'Penilaian not found'], 404);
        }

        if ($penilaian->dosen_id !== auth()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $penilaian->update([
            'proposal_ta_id' => $request->proposal_ta_id,
            'nilai' => $request->nilai,
        ]);
        return response()->json($penilaian);
    }

    public function destroy($id)
    {
        $penilaian = PenilaianProposalTA::find($id);
        if (is_null($penilaian)) {
            return response()->json(['message' => 'Penilaian not found'], 404);
        }

        $penilaian->delete();
        return response()->json(['message' => 'Penilaian deleted successfully']);
    }

    public function getPenilaianByMahasiswa()
    {
        $mahasiswaId = auth()->user()->id;

        // Ambil semua proposal TA yang dimiliki oleh mahasiswa yang sedang login
        $proposals = ProposalTA::where('mahasiswa_id', $mahasiswaId)->pluck('id');

        // Ambil semua penilaian untuk proposal TA tersebut
        $penilaian = PenilaianProposalTA::whereIn('proposal_ta_id', $proposals)->get();

        return response()->json([
            'status' => true,
            'message' => 'List Data penilaian proposal TA berhasil dimuat',
            'data' => $penilaian
        ]);
    }
}