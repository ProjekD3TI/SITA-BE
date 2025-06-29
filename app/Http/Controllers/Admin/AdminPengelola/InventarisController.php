<?php


namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Exports\InventarisExport;
use App\Helpers\PaginationHelper;
use App\Models\AdminPengelola\Inventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 
use App\Exports\AlatsExport;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use Maatwebsite\Excel\Facades\Excel;
class InventarisController extends Controller
{
    public function showInventaris(Request $request) {
        $query = Inventaris::query()
        ->leftJoin('jenis_inventaris', 'inventaris.id_jenis_inventaris', '=', 'jenis_inventaris.id_jenis_inventaris')
        ->leftJoin('ruangs', 'inventaris.id_ruang', '=', 'ruangs.id_ruang')
        ->select(
            'inventaris.*',
            'jenis_inventaris.nama_inventaris',
            'ruangs.nama_ruang'
        );

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('inventaris.no_inventaris', 'like', "%{$searchTerm}%")
                ->orWhere('jenis_inventaris.nama_inventaris', 'like', "%{$searchTerm}%")
                ->orWhere('ruangs.nama_ruang', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('inventaris.status', $request->status);
        }

        if ($request->has('kondisi') && $request->kondisi !== '') {
            $query->where('kondisi', $request->kondisi);
        }

        if ($request->has('year') && $request->year !== '') {
            $query->where('inventaris.tahun_pengadaan', $request->year);
        }
        
        $sortBy = $request->input('sort_by', 'created_at');
        $sortType = $request->input('sort_type', 'desc');

        $sortableColumns = [
            'no' => 'inventaris.id_inventaris', 
            'no_inventaris' => 'inventaris.no_inventaris',
            'nama_inventaris' => 'jenis_inventaris.nama_inventaris',
            'nama_ruang' => 'ruangs.nama_ruang',
            'kondisi' => 'inventaris.kondisi',
            'tahun_pengadaan' => 'inventaris.tahun_pengadaan',
            'status' => 'inventaris.status',
            'created_at' => 'inventaris.created_at',
        ];

        if (array_key_exists($sortBy, $sortableColumns)) {
            $sortColumn = $sortableColumns[$sortBy];
        } else {
            $sortColumn = 'inventaris.created_at';
        }

        $sortDirection = in_array(strtolower($sortType), ['asc', 'desc']) ? $sortType : 'desc';

        $query->orderBy($sortColumn, $sortDirection);
        
        $inventaris = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($inventaris, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Inventaris',
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

    public function createInventaris(Request $request) {
        $validator = Validator::make($request->all(), [
            'id_jenis_inventaris' => 'required',
            'no_inventaris' => 'required',
            'kondisi' => 'required',
            'id_ruang' => 'required',
            'tahun_pengadaan' => 'required',
            'jumlah' => 'sometimes|integer|min:1',
            
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $base_no_inv = $request->no_inventaris;
        $jumlah = $request->input('jumlah', 1);
        $inventaris_list = [];

        DB::beginTransaction();
        try {
            
            if ($jumlah == 1) {
                // Untuk inventaris tunggal - cek apakah nomor sudah ada
                $existing = Inventaris::where('no_inventaris', $base_no_inv)->first();
                
                if ($existing) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Nomor inventaris sudah ada',
                        'error' => "Nomor inventaris '{$base_no_inv}' sudah digunakan"
                    ], 400);
                }
                
                // Buat inventaris tunggal dengan nomor yang diinput user
                $inventaris = Inventaris::create([
                    'id_jenis_inventaris' => $request->id_jenis_inventaris,
                    'no_inventaris' => $base_no_inv,
                    'kondisi' => $request->kondisi,
                    'id_ruang' => $request->id_ruang,
                    'tahun_pengadaan' => $request->tahun_pengadaan,
                ]);

                // $url = env('FRONTEND_URL') . '/inventaris/' . $inventaris->id_inventaris;
                // $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
                // $writer = new Writer($renderer);
                // $qrCodeContent = $writer->writeString($url);

                // $qrFileName = Str::uuid() . '.svg';
                // $qrFilePath = "public/inventaris/qrcode/{$qrFileName}";
                // Storage::put($qrFilePath, $qrCodeContent);

                // $inventaris->qr_code_path = $qrFileName;
                // $inventaris->save();

                $inventaris_list[] = $inventaris;
                
            } else {
                // Untuk multiple inventaris - otomatis melanjutkan dari nomor terakhir
                
                // cari nomor  terakhir
                $last_inventaris = Inventaris::where('no_inventaris', 'LIKE', $base_no_inv . '%')
                    ->orderBy('no_inventaris', 'desc')
                    ->first();

                $start_number = 1;
                
                if ($last_inventaris) {
                    $last_no = $last_inventaris->no_inventaris;
                    
                    if (preg_match('/\.(\d{4})$/', $last_no, $matches)) {
                        $start_number = intval($matches[1]) + 1;
                    } 
                    // else {
                    //     $start_number = 1;
                    // }
                }

                for ($i = 0; $i < $jumlah; $i++) {
                    $current_no = $base_no_inv . '.' . str_pad($start_number + $i, 4, '0', STR_PAD_LEFT);

                    $inventaris = Inventaris::create([
                        'id_jenis_inventaris' => $request->id_jenis_inventaris,
                        'no_inventaris' => $current_no,
                        'kondisi' => $request->kondisi,
                        'id_ruang' => $request->id_ruang,
                        'tahun_pengadaan' => $request->tahun_pengadaan,
                    ]);

                    // $url = env('FRONTEND_URL') . '/inventaris/' . $inventaris->id_inventaris;
                    // $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
                    // $writer = new Writer($renderer);
                    // $qrCodeContent = $writer->writeString($url);

                    // $qrFileName = Str::uuid() . '.svg';
                    // $qrFilePath = "public/inventaris/qrcode/{$qrFileName}";
                    // Storage::put($qrFilePath, $qrCodeContent);

                    // $inventaris->qr_code_path = $qrFileName;
                    // $inventaris->save();

                    $inventaris_list[] = $inventaris;
                }
            }

            DB::commit();

            return response()->json([
                'message' => $jumlah > 1 ? 
                    "$jumlah inventaris berhasil ditambahkan" : 
                    'Inventaris berhasil ditambahkan',
                'data' => $inventaris_list
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan inventaris',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function findInventaris($id) {
        $inventaris = DB::table('inventaris')
            ->leftJoin('jenis_inventaris', 'inventaris.id_jenis_inventaris', '=', 'jenis_inventaris.id_jenis_inventaris')
            ->leftJoin('ruangs', 'inventaris.id_ruang', '=', 'ruangs.id_ruang')
            ->select(
                'inventaris.*',
                'jenis_inventaris.nama_inventaris',
                'ruangs.nama_ruang'
            )
            ->where('inventaris.id_inventaris', $id)
            ->first();

        if (!$inventaris) {
            return response()->json(['message' => 'inventaris tidak ditemukan'], 404);
        }
        
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data Inventaris',
            'data' => $inventaris
        ], 200);
        }

    public function editInventaris(Request $request, $id) {
        $db_inventaris = Inventaris::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_jenis_inventaris' => 'required', 
            'no_inventaris' => 'required',
            'kondisi' => 'required',
            'id_ruang' => 'required', 
            'tahun_pengadaan' => 'required', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = [
            'no_inventaris' => $request->no_inventaris,
            'kondisi' => $request->kondisi,
            'id_jenis_inventaris' => $request->id_jenis_inventaris,
            'id_ruang' => $request->id_ruang,
            'tahun_pengadaan' => $request->tahun_pengadaan,
        ];

        $db_inventaris->update($data);

        return response()->json(['message' => 'inventaris berhasil diupdate'], 200);
    }

    public function deleteInventaris($id) {
        $inventaris = Inventaris::findOrFail($id);
        
        if (!$inventaris) {
            return response()->json(['message' => 'inventaris tidak ditemukan'], 404);
        }

        $inventaris->delete();
        if ($inventaris->qr_code_path) {
            Storage::delete('public/inventaris/qrcode/' . $inventaris->qr_code_path);
        }
        

        return response()->json(['message' => 'inventaris berhasil dihapus']);
    }

    public function exportInventaris(Request $request) {
        $searchTerm = $request->input('search', null);
        $status = $request->input('status', null);
        $kondisi = $request->input('kondisi', null);
        $year = $request->input('year', null);
    
    
        return Excel::download(
            new InventarisExport($searchTerm, $status, $kondisi, $year),
            'inventaris.xlsx'
        );
    }

    public function downloadQrCode($id) {
        $inventaris = Inventaris::findOrFail($id);
        
        if (!$inventaris->qr_code_path) {
            return response()->json(['message' => 'QR Code tidak tersedia'], 404);
        }
        
        $path = storage_path('app/public/inventaris/qrcode/' . $inventaris->qr_code_path);
        
        if (!file_exists($path)) {
            return response()->json(['message' => 'File QR Code tidak ditemukan'], 404);
        }
        
        $filename = 'qrcode_inventaris_' . $inventaris->id_inventaris . '_' . str_replace(' ', '_', $inventaris->no_inventaris) . '.svg';
        
        return response()->download($path, $filename, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }
}