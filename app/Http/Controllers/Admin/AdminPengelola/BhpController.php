<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Models\AdminPengelola\Bhp;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 
use App\Exports\BhpExport;
use Maatwebsite\Excel\Facades\Excel;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Intervention\Image\Facades\Image;
class BhpController extends Controller
{
    public function showBhp(Request $request) {
        $query = Bhp::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_bhp', 'like', "%{$searchTerm}%")
                ->orWhere('peruntukan', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('peruntukan') && $request->peruntukan !== '') {
            $query->where('peruntukan', $request->peruntukan);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortType = $request->input('sort_type', 'desc');

        $sortableColumns = [
            'no' => 'id_bhp', 
            'nama_bhp' => 'nama_bhp',
            'peruntukan' => 'peruntukan',
            'kondisi_bagus' => 'kondisi_bagus',
            'kondisi_rusak' => 'kondisi_rusak',
            'created_at' => 'created_at',
        ];

        if (array_key_exists($sortBy, $sortableColumns)) {
            $sortColumn = $sortableColumns[$sortBy];
        } else {
            $sortColumn = 'created_at';
        }

        $sortDirection = in_array(strtolower($sortType), ['asc', 'desc']) ? $sortType : 'desc';

        $query->orderBy($sortColumn, $sortDirection);
        
        $bhp = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($bhp, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data BHP',
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

    public function createBhp(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama_bhp' => 'required',
            'kondisi_bagus' => 'required',
            'kondisi_rusak' => 'required',
            'peruntukan' => 'required',
            'foto_bhp' => 'required',
            'foto_bhp.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $bhp = Bhp::create([
            'nama_bhp' => $request->nama_bhp,
            'kondisi_bagus' => $request->kondisi_bagus,
            'kondisi_rusak' => $request->kondisi_rusak,
            'peruntukan' => $request->peruntukan,
        ]);

        // $url = env('FRONTEND_URL') . '/bhp/detail/' . $bhp->id_bhp;

        // $renderer = new ImageRenderer(
        //     new RendererStyle(400),
        //     new SvgImageBackEnd()
        // );
        // $writer = new Writer($renderer);
        // $qrCodeContent = $writer->writeString($url);

        // $qrFileName = Str::uuid() . '.svg';
        // $qrFilePath = "public/bhp/qrcode/{$qrFileName}";
        // Storage::put($qrFilePath, $qrCodeContent);

        // $bhp->qr_code_path = $qrFileName;
        // $bhp->save();

        if ($request->hasFile('foto_bhp')) {
        
            $foto_bhp = $request->file('foto_bhp');
        
            if (!is_array($foto_bhp)) {
                $foto_bhp = [$foto_bhp];
            }
        
            foreach ($foto_bhp as $foto) {
        
                $uuid = Str::uuid()->toString();
                $extension = $foto->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $path = $foto->storeAs('public/bhp', $filename);
        
                $bhp->fotos()->create([
                    'foto_path' => $filename,
                ]);
        
            }
        }

        return response()->json([
            'message' => 'Bhp berhasil ditambahkan',
            // 'qr_code_url' => url("storage/bhp/qrcode/{$qrFileName}"),
            // 'qr_code_download_url' => url("bhp/{$bhp->id_bhp}/download-qrcode"),
        ]);
    }

    public function findBhp($id) {
        $bhp = DB::table('bhps')->where('id_bhp', $id)->first();
        
        if (!$bhp) {
            return response()->json([
                'status' => 'error',
                'message' => 'BHP tidak ditemukan'
            ], 404);
        }
        
        $photos = DB::table('foto')
                    ->where('id_bhp', $id)
                    ->select('foto_id', 'foto_path')
                    ->get();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Detail BHP',
            'data' => $bhp,
            'photos' => $photos
        ], 200);
    }

    public function editBhp(Request $request, $id) {
        
            $db_bhp = Bhp::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_bhp' => 'required',
                'kondisi_bagus' => 'required',
                'kondisi_rusak' => 'required',
                'peruntukan' => 'required',
                'foto_bhp.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $data = [
                'nama_bhp' => $request->nama_bhp,
                'kondisi_bagus' => $request->kondisi_bagus,
                'kondisi_rusak' => $request->kondisi_rusak,
                'peruntukan' => $request->peruntukan,
            ];

            $db_bhp->update($data);

            if ($request->hasFile('foto_bhp')) {
                $db_bhp->fotos()->delete();
    
                $foto_bhp = $request->file('foto_bhp');
    
                foreach ($foto_bhp as $foto) {
                    $uuid = Str::uuid()->toString();
                    $extension = $foto->getClientOriginalExtension();
                    $filename = $uuid . '.' . $extension;
                    $path = $foto->storeAs('public/bhp', $filename);
    
                    $db_bhp->fotos()->create([
                        'foto_path' => $filename,
                    ]);
                }
            }

            return response()->json(['message' => 'Bhp berhasil diupdate'], 200);
        
    }

    public function deleteBhp($id) {
        $bhp = Bhp::findOrFail($id);

        if (!$bhp) {
            return response()->json(['message' => 'Bhp tidak ditemukan'], 404);
        }

        $bhp->delete();
        $bhp->fotos()->delete();
        if ($bhp->qr_code_path) {
            Storage::delete('public/bhp/qrcode/' . $bhp->qr_code_path);
        }

        return response()->json(['message' => 'Bhp berhasil dihapus']);
    }

    public function exportBhp(Request $request) {
        $searchTerm = $request->input('search', null);
        return Excel::download(new BhpExport($searchTerm), 'bhp.xlsx');
    }

    public function downloadQrCode($id) {
        $bhp = Bhp::findOrFail($id);
        
        if (!$bhp->qr_code_path) {
            return response()->json(['message' => 'QR Code tidak tersedia'], 404);
        }
        
        $path = storage_path('app/public/bhp/qrcode/' . $bhp->qr_code_path);
        
        if (!file_exists($path)) {
            return response()->json(['message' => 'File QR Code tidak ditemukan'], 404);
        }
        
        $filename = 'qrcode_bhp_' . $bhp->id_bhp . '_' . str_replace(' ', '_', $bhp->nama_bhp) . '.svg';
        
        return response()->download($path, $filename, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }
}