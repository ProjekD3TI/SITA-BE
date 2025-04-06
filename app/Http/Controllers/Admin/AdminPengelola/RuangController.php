<?php

namespace App\Http\Controllers\Admin\AdminPengelola;


use App\Helpers\PaginationHelper;
use App\Models\AdminPengelola\Ruang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 


class RuangController extends Controller
{
    public function showRuang(Request $request) {
        $query = Ruang::query()
        ->leftJoin('gedung', 'ruangs.id_gedung', '=', 'gedung.id_gedung')
        ->select(
            'ruangs.*',
            'gedung.nama_gedung'
        );

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('ruangs.nama_ruang', 'like', "%{$searchTerm}%")
                ->orWhere('gedung.nama_gedung', 'like', "%{$searchTerm}%")
                ->orWhere('ruangs.kapasitas', 'like', "%{$searchTerm}%")
                ->orWhere('ruangs.fasilitas', 'like', "%{$searchTerm}%");
            }
        );
        }
        
        $query->orderBy('created_at', 'desc');
        $ruang = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($ruang, $perPage, $page, [
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

    public function createRuang(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama_ruang' => 'required',
            'kapasitas' => 'required',
            'fasilitas' => 'required',
            'dimensi' => 'required',
            'id_gedung' => 'required',
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
            'status' => 1,
        ]);

        if ($request->hasFile('foto_ruang')) {
            $foto_ruang = $request->file('foto_ruang');
    
            foreach ($foto_ruang as $foto) {
                $uuid = Str::uuid()->toString();
                $extension = $foto->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $path = $foto->storeAs('public/ruang', $filename);
    
                $ruang->fotos()->create([
                    'foto_path' => $filename,
                ]);
            }
        }

        return response()->json(['message' => 'ruang berhasil ditambahkan']);

    }

    public function findRuang($id) {
        $ruang = DB::table('ruangs')
        ->leftJoin('gedung', 'ruangs.id_gedung', '=', 'gedung.id_gedung')
        ->leftJoin('lantai_gedung', 'gedung.id_lantai', '=', 'lantai_gedung.id_lantai')
        ->select(
            'ruangs.*',
            'gedung.nama_gedung',
            'lantai_gedung.nama_lantai'
        )
        ->where('ruangs.id_ruang', $id)
        ->first();
        
        if (!$ruang) {
            return response()->json([
                'status' => 'error',
                'message' => 'ruang tidak ditemukan'
            ], 404);
        }
        
        $photos = DB::table('foto')
                    ->where('id_ruang', $id)
                    ->select('foto_id', 'foto_path')
                    ->get();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Detail ruang',
            'data' => $ruang,
            'photos' => $photos
        ], 200);
    }


    public function editRuang(Request $request, $id) {
        
        $db_ruang = Ruang::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_ruang' => 'required',
            'kapasitas' => 'required',
            'fasilitas' => 'required',
            'dimensi' => 'required',
            'id_gedung' => 'required',
            'status' => 'required',
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
            'status' => $request->status,
        ];

        $db_ruang->update($data);

        if ($request->hasFile('foto_ruang')) {
            $db_ruang->fotos()->delete();

            $foto_ruang = $request->file('foto_ruang');

            foreach ($foto_ruang as $foto) {
                $uuid = Str::uuid()->toString();
                $extension = $foto->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $path = $foto->storeAs('public/ruang', $filename);

                $db_ruang->fotos()->create([
                    'foto_path' => $filename,
                ]);
            }
        }

        return response()->json(['message' => 'ruang berhasil diupdate'], 200);
    }
    

    public function deleteRuang($id) {
        $ruang = Ruang::findOrFail($id);

        if (!$ruang) {
            return response()->json(['message' => 'Ruang tidak ditemukan'], 404);
        }

        $ruang->delete();
        $ruang->fotos()->delete();

        return response()->json(['message' => 'Ruang berhasil dihapus']);
    }
    
}