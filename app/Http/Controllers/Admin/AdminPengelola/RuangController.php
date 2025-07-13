<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Models\AdminPengelola\Ruang;
use App\Models\AdminPengelola\Gedung;
use App\Models\AdminPengelola\LantaiGedung;
use App\Models\AdminPengelola\Foto; // Pastikan ada model Foto
use App\Helpers\PaginationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class RuangController extends Controller
{
    public function index()
{
    $ruang = Ruang::with(['gedung', 'lantai'])->get();
    return response()->json(['data' => $ruang]);
}
    // Ambil semua ruang dengan data gedung dan lantai
    public function showRuang(Request $request) {
        $query = Ruang::query()
            ->leftJoin('gedung', 'ruangs.id_gedung', '=', 'gedung.id_gedung')
            ->leftJoin('lantai_gedung', 'ruangs.id_lantai', '=', 'lantai_gedung.id_lantai')
            ->select('ruangs.*', 'gedung.nama_gedung', 'lantai_gedung.nama_lantai');
    
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('ruangs.nama_ruang', 'like', "%{$searchTerm}%")
                  ->orWhere('gedung.nama_gedung', 'like', "%{$searchTerm}%")
                  ->orWhere('lantai_gedung.nama_lantai', 'like', "%{$searchTerm}%")
                  ->orWhere('ruangs.kapasitas', 'like', "%{$searchTerm}%")
                  ->orWhere('ruangs.fasilitas', 'like', "%{$searchTerm}%");
            });
        }
    
        $query->orderBy('ruangs.created_at', 'desc');
        $ruangs = $query->get();
    
        // Tambahkan display name dan susunan output
        $ruangs->transform(function ($item) {
            $item->display_name = $item->nama_ruang;
    
            if (isset($item->nama_gedung)) {
                $item->display_name .= ' , ' . $item->nama_gedung;
            }
    
            if (isset($item->nama_lantai)) {
                $item->display_name .= ', ' . $item->nama_lantai;
            }
    
            return $item;
        });
    
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
    
        $paginatedData = PaginationHelper::paginate($ruangs, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Data ruang',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }
    

    // Ambil lantai berdasarkan gedung
    public function getLantaiByGedung($id_gedung)
    {
        $lantai = LantaiGedung::where('id_gedung', $id_gedung)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar lantai berdasarkan gedung',
            'data' => $lantai
        ]);
    }

    // Buat ruang baru
    public function createRuang(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama_ruang' => 'required',
            'kapasitas' => 'required',
            'fasilitas' => 'required',
            'dimensi' => 'required',
            'id_gedung' => 'required|exists:gedung,id_gedung',
            'id_lantai' => 'required|exists:lantai_gedung,id_lantai',
            'foto_ruang' => 'required',
            'foto_ruang.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $ruang = Ruang::create([
            'nama_ruang' => $request->nama_ruang,
            'kapasitas' => $request->kapasitas,
            'fasilitas' => $request->fasilitas,
            'dimensi' => $request->dimensi,
            'id_gedung' => $request->id_gedung,
            'id_lantai' => $request->id_lantai,
            'status' => 1,
        ]);

        // Upload foto
        foreach ($request->file('foto_ruang') as $foto) {
            $filename = Str::uuid() . '.' . $foto->getClientOriginalExtension();
            $foto->storeAs('public/ruang', $filename);

            $ruang->fotos()->create(['foto_path' => $filename]);
        }

        return response()->json(['message' => 'Ruang berhasil ditambahkan'], 201);
    }

    // Ambil ruang by ID
    public function findRuang($id) {
        $ruang = Ruang::with(['gedung', 'lantai'])->find($id);

        if (!$ruang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ruang tidak ditemukan'
            ], 404);
        }

        $photos = $ruang->fotos()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Detail ruang',
            'data' => $ruang,
            'photos' => $photos
        ], 200);
    }

    // Edit ruang
    public function editRuang(Request $request, $id) {
        $db_ruang = Ruang::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_ruang' => 'required',
            'kapasitas' => 'required',
            'fasilitas' => 'required',
            'dimensi' => 'required',
            'id_gedung' => 'required',
            'id_lantai' => 'required',
            // 'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = [
            'nama_ruang' => $request->nama_ruang,
            'kapasitas' => $request->kapasitas,
            'dimensi' => $request->dimensi,
            'fasilitas' => $request->fasilitas,
            'id_gedung' => $request->id_gedung,
            'id_lantai' => $request->id_lantai,
            // 'status' => $request->status,
        ];

        $db_ruang->update($data);

        // Ganti foto
        if ($request->hasFile('foto_ruang')) {
            $db_ruang->fotos()->delete();

            foreach ($request->file('foto_ruang') as $foto) {
                $filename = Str::uuid() . '.' . $foto->getClientOriginalExtension();
                $foto->storeAs('public/ruang', $filename);
                $db_ruang->fotos()->create(['foto_path' => $filename]);
            }
        }

        return response()->json(['message' => 'Ruang berhasil diupdate'], 200);
    }

    // Hapus ruang
    public function deleteRuang($id) {
        $ruang = Ruang::findOrFail($id);

        $ruang->fotos()->delete();
        $ruang->delete();

        return response()->json(['message' => 'Ruang berhasil dihapus']);
    }
}