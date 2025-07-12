<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Models\AdminPengelola\JenisInventaris;
use Illuminate\Http\Request;
use App\Models\Foto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 
use App\Exports\JenisInventarisExport;
use Maatwebsite\Excel\Facades\Excel;

class JenisInventarisController extends Controller

{
    public function showJenisInventaris(Request $request) {
        $query = JenisInventaris::query()
        ->leftJoin('merk', 'jenis_inventaris.id_merk', '=', 'merk.id_merk')
        ->leftJoin('tipe', 'jenis_inventaris.id_tipe', '=', 'tipe.id_tipe')
        ->select(
            'jenis_inventaris.*',
            'merk.nama_merk',
            'tipe.nama_tipe'
        );

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('jenis_inventaris.nama_inventaris', 'like', "%{$searchTerm}%")
                ->orWhere('merk.nama_merk', 'like', "%{$searchTerm}%")
                ->orWhere('tipe.nama_tipe', 'like', "%{$searchTerm}%")
                ->orWhere('jenis_inventaris.peruntukan', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('status_import') && $request->status_import !== '') {
            $query->where('status_import', $request->status_import);
        }

        if ($request->has('peruntukan') && $request->peruntukan !== '') {
            $query->where('peruntukan', $request->peruntukan);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortType = $request->input('sort_type', 'desc');

        $sortableColumns = [
            'no' => 'jenis_inventaris.id_jenis_inventaris', 
            'nama_inventaris' => 'jenis_inventaris.nama_inventaris',
            'nama_merk' => 'merk.nama_merk',
            'nama_tipe' => 'tipe.nama_tipe',
            'peruntukan' => 'jenis_inventaris.peruntukan',
            'status_import' => 'jenis_inventaris.status_import',
            'created_at' => 'jenis_inventaris.created_at',
        ];

        if (array_key_exists($sortBy, $sortableColumns)) {
            $sortColumn = $sortableColumns[$sortBy];
        } else {
            $sortColumn = 'jenis_inventaris.created_at';
        }

        $sortDirection = in_array(strtolower($sortType), ['asc', 'desc']) ? $sortType : 'desc';

        $query->orderBy($sortColumn, $sortDirection);
        
        $jenis_inventaris = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($jenis_inventaris, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);


        return response()->json([
            'status' => 'success',
            'message' => 'Data Jenis Inventaris',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ],
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_type' => $sortType,
        ]
        ], 200);
    }

    

    public function createJenisInventaris(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama_inventaris' => 'required',
            'id_merk' => 'required',
            'id_tipe' => 'required',
            'peruntukan' => 'required',
            'status_import' => 'required',
            'foto_inventaris' => 'required',
            'foto_inventaris.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $jenis_inventaris = JenisInventaris::create([
            'nama_inventaris' => $request->nama_inventaris,
            'id_merk' => $request->id_merk,
            'id_tipe' => $request->id_tipe,
            'peruntukan' => $request->peruntukan,
            'status_import' => $request->status_import,
        ]);

        if ($request->hasFile('foto_inventaris')) {
            $foto_inventaris = $request->file('foto_inventaris');
    
            foreach ($foto_inventaris as $foto) {
                $uuid = Str::uuid()->toString();
                $extension = $foto->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $path = $foto->storeAs('public/inventaris', $filename);
    
                $jenis_inventaris->fotos()->create([
                    'foto_path' => $filename,
                ]);
            }
        }

        return response()->json(['message' => 'Inventaris berhasil ditambahkan']);
    }

    public function findJenisInventaris($id) {
        $inventaris = DB::table('jenis_inventaris')
        ->leftJoin('merk', 'jenis_inventaris.id_merk', '=', 'merk.id_merk')
        ->leftJoin('tipe', 'jenis_inventaris.id_tipe', '=', 'tipe.id_tipe')
        ->select(
            'jenis_inventaris.*',
            'merk.nama_merk',
            'tipe.nama_tipe'
        )
        ->where('jenis_inventaris.id_jenis_inventaris', $id)
        ->first();
        
        if (!$inventaris) {
            return response()->json([
                'status' => 'error',
                'message' => 'inventaris tidak ditemukan'
            ], 404);
        }

        //hitung total inventaris di jenis inventaris
        $inventaris->total_inventaris = DB::table('inventaris')
            ->where('id_jenis_inventaris', $id)
            ->count();
        
        $photos = DB::table('foto')
                    ->where('id_jenis_inventaris', $id)
                    ->select('foto_id', 'foto_path')
                    ->get();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Detail inventaris',
            'data' => $inventaris,
            'photos' => $photos,
            'total_inventaris' => $inventaris->total_inventaris
        ], 200);
    }

    public function editJenisInventaris(Request $request, $id) {
        $db_jenis_inventaris = JenisInventaris::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_inventaris' => 'required',
            'id_merk' => 'required',
            'id_tipe' => 'required',
            'peruntukan' => 'required',
            'status_import' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = [
            'nama_inventaris' => $request->nama_inventaris,
            'id_merk' => $request->id_merk,
            'id_tipe' => $request->id_tipe,
            'peruntukan' => $request->peruntukan,
            'status_import' => $request->status_import,
        ];

        $db_jenis_inventaris->update($data);

        if ($request->hasFile('foto_inventaris')) {
            $db_jenis_inventaris->fotos()->delete();

            $foto_inventaris = $request->file('foto_inventaris');

            foreach ($foto_inventaris as $foto) {
                $uuid = Str::uuid()->toString();
                $extension = $foto->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $path = $foto->storeAs('public/inventaris', $filename);

                $db_jenis_inventaris->fotos()->create([
                    'foto_path' => $filename,
                ]);
            }
        }

        return response()->json(['message' => 'jenis inventaris berhasil diupdate'], 200);
    }

    public function deleteJenisInventaris($id) {
        $jenis_inventaris = JenisInventaris::findOrFail($id);

        if (!$jenis_inventaris) {
            return response()->json(['message' => 'jenis inventaris tidak ditemukan'], 404);
        }

        $jenis_inventaris->delete();
        $jenis_inventaris->fotos()->delete();

        return response()->json(['message' => 'jenis inventaris berhasil dihapus']);
    }
    public function exportJenisInventaris(Request $request) {
        $searchTerm = $request->input('search', null);
        $statusImport = $request->input('status_import', null);
        $peruntukan = $request->input('peruntukan', null);


        return Excel::download(
            new JenisInventarisExport($searchTerm, $statusImport, $peruntukan),
            'jenis_inventaris.xlsx'
        );
    }
}