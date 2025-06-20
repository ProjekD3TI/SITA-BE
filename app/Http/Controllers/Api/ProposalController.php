<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Kategori;
use App\Models\Proposal;
use App\Models\StatusForm;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use App\Exports\ProposalExport;
use App\Http\Responses\ApiResponse;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProposalResource;
use Illuminate\Validation\ValidationException;

class ProposalController extends Controller
{
    // ========================== FITUR ADMIN ==========================

    // Fitur Proposal (List Pengajuan Proposal & Detail Pengajuan Proposal & Validasi Berkas)

    public function tabelListPengajuanProposalForAdmin(Request $request)
    {
        try {
            $statusForm = StatusForm::where('nama_form', 'proposal')
                ->select('id', 'is_open')
                ->first();

            $query = Proposal::with([
                'tugasAkhir.mahasiswa:id,nim,name',
                'berkas'
            ]);

            // Fitur search nim/nama mahasiswa atau judul proposal
            if ($request->search) {
                $query->whereHas('tugasAkhir.mahasiswa', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('nim', 'like', '%' . $request->search . '%');
                })
                    ->orWhere('judul_proposal', 'like', '%' . $request->search . '%');
            }

            // Fitur filter status proposal (0=menunggu, 1=disetujui, 2=ditolak)
            if ($request->has('status_proposal') && $request->status_proposal !== '') {
                $statusList = explode(',', $request->status_proposal);
                $statusList = array_map('trim', $statusList);
                $query->whereIn('status_proposal', $statusList);
            }
            // Urutkan: status_proposal = 0 (menunggu) paling atas, lalu id desc
            $query->orderByRaw("CASE WHEN status_proposal = 0 THEN 0 ELSE 1 END")->orderByDesc('id');

            $listProposal = $query->paginate(10);
            $notificationCount = Proposal::where('status_proposal', 0)->count();

            $data = collect($listProposal->items())->map(function ($item) {
                Carbon::setLocale('id');
                return [
                    'proposal_id' => $item->id,
                    'judul_proposal' => $item->judul_proposal ?? null,
                    'status_proposal' => $item->status_proposal ?? null,
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->tugasAkhir->mahasiswa->id ?? null,
                        'nim' => $item->tugasAkhir->mahasiswa->nim ?? null,
                        'name' => $item->tugasAkhir->mahasiswa->name ?? null,
                    ],
                    'catatan_perbaikan' => $item->catatan_perbaikan ?? null,
                    'tanggal_pengajuan' => $item->tanggal_unggah
                        ? Carbon::parse($item->tanggal_unggah)->translatedFormat('l, d F Y')
                        : null,
                    'berkas' => $item->berkas ? $item->berkas->map(function ($berkas) {
                        return [
                            'berkas_id' => $berkas->id,
                            'nama_berkas' => $berkas->nama_berkas,
                            'file_path' => $berkas->file_path,
                            'download_url' => route('berkas.download', ['berkas' => $berkas->id]),
                            'status_valid' => $berkas->status_valid,
                        ];
                    })->values() : [],
                ];
            });

            $result = [
                'notification' => $notificationCount,
                'status_form' => [
                    'id' => $statusForm->id,
                    'is_open' => $statusForm->is_open,
                ],
                'data' => $data,
                'pagination' => [
                    'current_page' => $listProposal->currentPage(),
                    'last_page' => $listProposal->lastPage(),
                    'per_page' => $listProposal->perPage(),
                    'total' => $listProposal->total(),
                ],
            ];

            return ApiResponse::success($result, 'Data list proposal & kategori berhasil diambil');

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function detailPengajuanProposalForAdmin($proposalId)
    {
        try {
            $proposal = Proposal::with([
                'tugasAkhir.mahasiswa:id,nim,name',
                'tugasAkhir.kategori:id,nama_kategori',
                'tugasAkhir.tahunAjaran:id,tahun_ajaran',
                'berkas'
            ])->find($proposalId);

            if (!$proposal) {
                return ApiResponse::error('Proposal tidak ditemukan untuk ID tersebut', 404);
            }

            $data = [
                'proposal_id' => $proposal->id,
                'mahasiswa' => [
                    'mahasiswa_id' => $proposal->tugasAkhir->mahasiswa->id ?? null,
                    'nim' => $proposal->tugasAkhir->mahasiswa->nim ?? null,
                    'name' => $proposal->tugasAkhir->mahasiswa->name ?? null,
                ],
                'judul_proposal' => $proposal->judul_proposal ?? null,
                'kategori' => [
                    'kategori_id' => $proposal->tugasAkhir->kategori->id ?? null,
                    'nama_kategori' => $proposal->tugasAkhir->kategori->nama_kategori ?? null,
                ],
                'tahun_ajaran' => [
                    'tahun_ajaran_id' => $proposal->tugasAkhir->tahunAjaran->id ?? null,
                    'tahun_ajaran' => $proposal->tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                ],
                'catatan_perbaikan' => $proposal->catatan_perbaikan ?? null,
                'berkas' => $proposal->berkas ? $proposal->berkas->map(function ($berkas) {
                    return [
                        'id' => $berkas->id,
                        'nama_berkas' => $berkas->nama_berkas,
                        'file_path' => $berkas->file_path,
                        'download_url' => route('berkas.download', ['berkas' => $berkas->id]),
                        'status_valid' => $berkas->status_valid,
                    ];
                })->values() : [],
            ];

            return ApiResponse::success($data, 'Data proposal berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function updateValidationStatusForAdmin(Request $request, $id)
    {
        try {
            // Validasi: array status_valid_berkas harus ada dan berisi 2 item (hanya 1 atau 2)
            $validatedData = $request->validate([
                'status_valid_berkas' => 'required|array|size:2',
                'status_valid_berkas.*' => 'required|in:1,2',
                'catatan_perbaikan' => 'nullable|string', // Tambahkan validasi catatan_perbaikan
            ]);

            $proposal = Proposal::with(['berkas'])->findOrFail($id);

            // Update status_valid pada masing-masing berkas
            $berkasList = $proposal->berkas;
            foreach ($berkasList as $i => $berkas) {
                // urutan status_valid_berkas harus sesuai urutan berkas di FE
                $berkas->status_valid = $validatedData['status_valid_berkas'][$i];
                $berkas->save();
            }

            // Cek status proposal berdasarkan status_valid semua berkas
            $statusList = $validatedData['status_valid_berkas'];
            if (in_array(2, $statusList)) {
                $proposal->status_proposal = 2; // Ditolak
            } elseif (count(array_unique($statusList)) === 1 && $statusList[0] == 1) {
                $proposal->status_proposal = 1; // Diterima
            }
            // Tidak boleh ada status 0, karena sudah divalidasi di atas

            // Tambahkan update catatan_perbaikan jika ada
            if (array_key_exists('catatan_perbaikan', $validatedData)) {
                $proposal->catatan_perbaikan = $validatedData['catatan_perbaikan'];
            }

            $proposal->save();

            return ApiResponse::success(new ProposalResource($proposal->load('tugasAkhir', 'berkas')), 'Status proposal & berkas berhasil diperbarui');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    // Fitur Proposal (List Seminar Proposal & Detail Seminar Proposal & Update Nilai dan Catatan)

    public function tabelListSeminarProposalForAdmin(Request $request)
    {
        try {
            $query = Proposal::with([
                'tugasAkhir.mahasiswa:id,nim,name',
                'tugasAkhir.kategori',
                'tugasAkhir.dosenPembimbing:id,name', // agar FE dapat dospem
            ]);


            // Fitur search nim/nama mahasiswa atau judul proposal
            if ($request->search) {
                $query->whereHas('tugasAkhir.mahasiswa', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('nim', 'like', '%' . $request->search . '%');
                })
                    ->orWhere('judul_proposal', 'like', '%' . $request->search . '%');
            }

            // Tambahkan filter status_proposal = 1
            $query->where('status_proposal', 1);

            // Filter status_nilai: sudah/ belum
            if ($request->has('status_nilai')) {
                if ($request->status_nilai === 'sudah') {
                    $query->whereHas('tugasAkhir', function ($q) {
                        $q->whereNotNull('nilai_proposal');
                    });
                } elseif ($request->status_nilai === 'belum') {
                    $query->whereHas('tugasAkhir', function ($q) {
                        $q->whereNull('nilai_proposal');
                    });
                }
            }
            $notificationCount = TugasAkhir::whereNull('nilai_proposal')
                ->whereHas('proposal', function ($q) {
                    $q->where('status_proposal', 1);
                })->count();

            // Urutkan: yang belum ada nilai_proposal di atas, lalu id desc
            $query->orderByRaw(" CASE WHEN ( SELECT nilai_proposal FROM tugas_akhir WHERE tugas_akhir.proposal_id = proposal.id LIMIT 1 ) IS NULL THEN 0 ELSE 1 END ")->orderByDesc('id');
            $listProposal = $query->paginate(10);

            $data = collect($listProposal->items())->map(function ($item) {
                Carbon::setLocale('id');
                return [
                    'proposal_id' => $item->id,
                    'judul_proposal' => $item->judul_proposal ?? null,
                    'nilai_proposal' => $item->tugasAkhir->nilai_proposal ?? null,
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->tugasAkhir->mahasiswa->id ?? null,
                        'nim' => $item->tugasAkhir->mahasiswa->nim ?? null,
                        'name' => $item->tugasAkhir->mahasiswa->name ?? null,
                    ],
                    'dosen_pembimbing' => $item->tugasAkhir->dosenPembimbing ? [
                        'id' => $item->tugasAkhir->dosenPembimbing->id,
                        'name' => $item->tugasAkhir->dosenPembimbing->name,
                    ] : null,
                ];
            });

            $result = [
                'notification' => $notificationCount,
                'data' => $data,
                'pagination' => [
                    'current_page' => $listProposal->currentPage(),
                    'last_page' => $listProposal->lastPage(),
                    'per_page' => $listProposal->perPage(),
                    'total' => $listProposal->total(),
                ],
            ];

            return ApiResponse::success($result, 'Data list seminar proposal berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function detailSeminarProposalForAdmin($proposalId)
    {
        try {
            $proposal = Proposal::with([
                'tugasAkhir.mahasiswa:id,nim,name',
                'tugasAkhir.kategori:id,nama_kategori',
                'tugasAkhir.tahunAjaran:id,tahun_ajaran',
                'berkas'
            ])
                ->where('status_proposal', 1) // Hanya ambil proposal yang sudah disetujui
                ->find($proposalId);

            if (!$proposal) {
                return ApiResponse::success(null, 'Proposal tidak ditemukan untuk ID tersebut');
            }
            $naskahBerkas = $proposal->berkas
                ->where('nama_berkas', 'Naskah Proposal Tugas Akhir')
                ->map(function ($berkas) {
                    return [
                        'id' => $berkas->id,
                        'nama_berkas' => $berkas->nama_berkas,
                        'file_path' => $berkas->file_path,
                        'download_url' => route('berkas.download', ['berkas' => $berkas->id]),
                    ];
                })
                ->values();

            $data = [
                'proposal_id' => $proposal->id,
                'judul_proposal' => $proposal->judul_proposal ?? null,
                'mahasiswa' => [
                    'mahasiswa_id' => $proposal->tugasAkhir->mahasiswa->id ?? null,
                    'nim' => $proposal->tugasAkhir->mahasiswa->nim ?? null,
                    'name' => $proposal->tugasAkhir->mahasiswa->name ?? null,
                ],
                'kategori' => [
                    'kategori_id' => $proposal->tugasAkhir->kategori->id ?? null,
                    'nama_kategori' => $proposal->tugasAkhir->kategori->nama_kategori ?? null,
                ],
                'tahun_ajaran' => [
                    'tahun_ajaran_id' => $proposal->tugasAkhir->tahunAjaran->id ?? null,
                    'tahun_ajaran' => $proposal->tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                ],
                'dosen_pembimbing' => $proposal->tugasAkhir->dosenPembimbing ? [
                    'id' => $proposal->tugasAkhir->dosenPembimbing->id,
                    'name' => $proposal->tugasAkhir->dosenPembimbing->name,
                ] : null,
                'nilai_proposal' => $proposal->tugasAkhir->nilai_proposal ?? null,
                'catatan_proposal' => $proposal->tugasAkhir->catatan_proposal ?? null,
                'berkas' => $naskahBerkas, // Hanya berkas Naskah Proposal Tugas Akhir
            ];

            return ApiResponse::success($data, 'Data seminar proposal berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function updateNilaidanCatatanForAdmin(Request $request, $id)
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


            // $tugasAkhir->catatan_proposal = $validatedData['catatan_proposal'];
            $tugasAkhir->catatan_proposal = Purifier::clean($validatedData['catatan_proposal']);
            $tugasAkhir->save();

            // Return the updated TugasAkhir using a resource
            return ApiResponse::success(new ProposalResource($proposal->load('tugasAkhir')), 'Nilai dan catatan proposal berhasil diperbarui');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi Gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }

    public function exportProposalBelumSeminar(Request $request)
    {
        $search = $request->get('search');
        $statusNilai = $request->get('status_nilai');

        return Excel::download(
            new ProposalExport($search, $statusNilai),
            'seminar_proposal_filtered.xlsx'
        );
    }


    // ========================== END FITUR ADMIN ==========================



    // ========================== FITUR MAHASISWA ==========================

    // Fitur Proposal Admin TA

    public function storeForMahasiswa(Request $request)
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
            $statusForm = StatusForm::where('nama_form', 'proposal')->first();

            if ($statusForm->is_open !== 1) {
                return ApiResponse::error('Pengajuan proposal tidak dibuka saat ini', 400);
            }

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

    public function updateForMahasiswa(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'judul_proposal' => 'required|string',
                'file_proposal' => 'nullable|file|mimes:pdf',
                'file_transkrip' => 'nullable|file|mimes:pdf',
            ]);

            $proposal = Proposal::with(['tugasAkhir', 'berkas'])->findOrFail($id);

            $proposal->judul_proposal = $validatedData['judul_proposal'];
            $isUpload = false;

            // Update berkas proposal jika ada file baru
            if ($request->hasFile('file_proposal')) {
                $berkasProposal = $proposal->berkas()->where('nama_berkas', 'Naskah Proposal Tugas Akhir')->first();
                if ($berkasProposal) {
                    // Hapus file lama jika ada
                    if ($berkasProposal->file_path && Storage::exists(ltrim($berkasProposal->file_path, '/'))) {
                        Storage::delete(ltrim($berkasProposal->file_path, '/'));
                    }
                    $originalName = $request->file('file_proposal')->getClientOriginalName();
                    $filePath = $request->file('file_proposal')->storeAs("private/proposal/{$proposal->id}", $originalName);
                    $privatePath = "/private/proposal/{$proposal->id}/{$originalName}";
                    $berkasProposal->file_path = $privatePath;
                    $berkasProposal->status_valid = 0;
                    $berkasProposal->save();
                    $isUpload = true;
                }
            }

            if ($request->hasFile('file_transkrip')) {
                $berkasTranskrip = $proposal->berkas()->where('nama_berkas', 'Transkrip Nilai Mahasiswa')->first();
                if ($berkasTranskrip) {
                    // Hapus file lama jika ada
                    if ($berkasTranskrip->file_path && Storage::exists(ltrim($berkasTranskrip->file_path, '/'))) {
                        Storage::delete(ltrim($berkasTranskrip->file_path, '/'));
                    }
                    $originalName = $request->file('file_transkrip')->getClientOriginalName();
                    $filePath = $request->file('file_transkrip')->storeAs("private/proposal/{$proposal->id}", $originalName);
                    $privatePath = "/private/proposal/{$proposal->id}/{$originalName}";
                    $berkasTranskrip->file_path = $privatePath;
                    $berkasTranskrip->status_valid = 0;
                    $berkasTranskrip->save();
                    $isUpload = true;
                }
            }

            // Jika ada upload ulang file, set status_proposal ke 0 (menunggu validasi)
            if ($isUpload) {
                $proposal->status_proposal = 0;
            }

            $proposal->save();

            return ApiResponse::success(new ProposalResource($proposal->load('tugasAkhir', 'berkas')), 'Proposal berhasil diubah');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    public function proposalForMahasiswa(Request $request)
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

            $statusForm = StatusForm::where('nama_form', 'proposal')->first();

            if (!$tugasAkhir) {
                // Kembalikan data kosong/null agar FE bisa handle
                return ApiResponse::success([
                    'tugas_akhir' => null,
                    'proposal' => null,
                ], 'Belum ada tugas akhir aktif');
            }

            $proposal = $tugasAkhir->proposal;

            $result = [
                'status_form' => [
                    'id' => $statusForm->id,
                    'is_open' => $statusForm->is_open,
                ],
                'tugas_akhir' => [
                    'id' => $tugasAkhir->id,
                    'dosen_pembimbing' => $tugasAkhir->dosenPembimbing ? [
                        'id' => $tugasAkhir->dosenPembimbing->id,
                        'nama' => $tugasAkhir->dosenPembimbing->name,
                    ] : null,
                    'kategori' => $tugasAkhir->kategori ? [
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
                    'catatan_perbaikan' => $proposal->catatan_perbaikan, // <-- Tambahkan ini
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
        } catch (Exception $e) {
            return ApiResponse::error('Gagal mengambil data proposal: ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR MAHASISWA ==========================

}