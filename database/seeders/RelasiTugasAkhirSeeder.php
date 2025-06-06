<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestPembimbing;
use App\Models\Bimbingan;
use App\Models\NilaiBimbingan;
use App\Models\UjianTA;
use App\Models\NilaiUjianTA;
use App\Models\TugasAkhir;
use App\Models\Dosen;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\Proposal;
use App\Models\Berkas;

class RelasiTugasAkhirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dosenIds = Dosen::pluck('id')->toArray();
        $semesterIds = Semester::pluck('id')->toArray();
        $tahunAjaranIds = TahunAjaran::pluck('id')->toArray();
        $kategoriId = 1;

        // Ambil semua id mahasiswa
        $mahasiswaIds = \App\Models\User::pluck('id')->toArray();
        shuffle($mahasiswaIds);
        $loopCount = min(count($mahasiswaIds), 4);

        for ($n = 0; $n < $loopCount; $n++) {
            $mahasiswaId = $mahasiswaIds[$n];
            $semesterId = $semesterIds ? $semesterIds[array_rand($semesterIds)] : null;
            $tahunAjaranId = $tahunAjaranIds ? $tahunAjaranIds[array_rand($tahunAjaranIds)] : null;

            // 1. Mahasiswa request pembimbing (status_request random 0,1,2)
            $statusRequest = rand(0, 2);
            $requestedDosenId = $dosenIds[array_rand($dosenIds)];

            // 2. Admin plot dosen pembimbing jika status_request == 2, jika 1 sesuai request, jika 0 tidak diproses
            $dosenPembimbingId = null;
            if ($statusRequest == 1) {
                $dosenPembimbingId = $requestedDosenId;
            } elseif ($statusRequest == 2) {
                $dosenPembimbingId = $dosenIds[array_rand($dosenIds)];
            }

            // 3. Buat Tugas Akhir (hanya jika status_request != 0)
            if ($statusRequest != 0) {
                $ta = TugasAkhir::create([
                    'mahasiswa_id' => $mahasiswaId,
                    'semester_id' => $semesterId,
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'kategori_id' => $kategoriId,
                    'judul_tugas_akhir' => "Judul Tugas Akhir " . ($n + 1),
                    'catatan_proposal' => "Catatan proposal TA " . ($n + 1),
                    'nilai_proposal' => rand(70, 95),
                    'nilai_akhir' => rand(70, 95),
                    'status_kelulusan' => rand(0, 1),
                    'status_valid' => rand(0, 1),
                    'status_aktif' => 1,
                    'dosen_pembimbing_id' => $dosenPembimbingId,
                ]);
            } else {
                // Jika status_request 0, TA belum dibuat
                $ta = null;
            }

            // 4. Simpan request pembimbing
            if ($ta) {
                RequestPembimbing::create([
                    'tugas_akhir_id' => $ta->id,
                    'dosen_id' => $requestedDosenId,
                    'status_request' => $statusRequest,
                    'tanggal_request' => now()->subDays(rand(10, 30)),
                    'tanggal_respon' => now()->subDays(rand(1, 9)),
                ]);
            }

            // 5. Jika TA sudah ada, mahasiswa bisa bimbingan dan daftar proposal
            if ($ta) {
                // Bimbingan (acak 8-12, agar bisa lebih dari 9)
                $bimbinganCount = rand(8, 12);
                for ($i = 1; $i <= $bimbinganCount; $i++) {
                    Bimbingan::create([
                        'tugas_akhir_id' => $ta->id,
                        'catatan' => "Catatan bimbingan ke-$i untuk TA {$ta->id}",
                        'bab' => $i,
                        'status_verifikasi' => 1,
                        'tanggal_bimbingan' => now()->subDays(rand(1, 60)),
                    ]);
                }

                // Nilai Bimbingan
                $nilaiBimbingan = NilaiBimbingan::create([
                    'rerata_bimbingan' => rand(70, 95),
                    'disiplin' => rand(70, 95),
                    'komunikasi' => rand(70, 95),
                    'skill_pengetahuan' => rand(70, 95),
                    'tingkah_laku' => rand(70, 95),
                ]);
                $ta->nilai_bimbingan_id = $nilaiBimbingan->id;
                $ta->save();

                // Proposal (bisa diterima/ditolak, diberi catatan)
                $statusProposal = rand(0, 1); // 0: ditolak, 1: diterima
                $proposal = Proposal::create([
                    'judul_proposal' => "Judul Proposal TA " . ($n + 1),
                    'status_proposal' => $statusProposal,
                    'catatan_perbaikan' => $statusProposal ? null : 'Perbaiki bab 2 dan 3',
                    'tanggal_unggah' => now()->subDays(rand(1, 30)),
                ]);
                $ta->proposal_id = $proposal->id;
                $ta->save();

                // Berkas Proposal
                Berkas::create([
                    'berkasable_type' => 'App\Models\Proposal',
                    'berkasable_id' => $proposal->id,
                    'nama_berkas' => 'Naskah Proposal Tugas Akhir',
                    'file_path' => "/private/proposal/{$proposal->id}/Naskah_Proposal_TA_" . ($n + 1) . ".pdf",
                    'status_valid' => 1,
                ]);
                Berkas::create([
                    'berkasable_type' => 'App\Models\Proposal',
                    'berkasable_id' => $proposal->id,
                    'nama_berkas' => 'Transkrip Nilai Mahasiswa',
                    'file_path' => "/private/proposal/{$proposal->id}/Transkrip_Nilai_" . ($n + 1) . ".pdf",
                    'status_valid' => 1,
                ]);

                // 6. Jika bimbingan > 9, admin plotting dosen penguji
                if ($bimbinganCount > 9) {
                    // Pilih 2 dosen penguji acak, pastikan tidak sama dengan dosen pembimbing
                    $pengujiIds = array_diff($dosenIds, [$dosenPembimbingId]);
                    $pengujiIds = array_values($pengujiIds);
                    shuffle($pengujiIds);
                    $penguji1 = $pengujiIds[0] ?? $dosenPembimbingId;
                    $penguji2 = $pengujiIds[1] ?? $dosenPembimbingId;

                    // Mahasiswa daftar ujian TA (ajukan berkas tugas akhir)
                    // Berkas Tugas Akhir
                    Berkas::create([
                        'berkasable_type' => 'App\Models\TugasAkhir',
                        'berkasable_id' => $ta->id,
                        'nama_berkas' => 'Naskah Tugas Akhir',
                        'file_path' => "/private/tugas_akhir/{$ta->id}/Naskah_TA_" . ($n + 1) . ".pdf",
                        'status_valid' => rand(0, 1),
                    ]);
                    Berkas::create([
                        'berkasable_type' => 'App\Models\TugasAkhir',
                        'berkasable_id' => $ta->id,
                        'nama_berkas' => 'Scan KRS Semester Akhir',
                        'file_path' => "/private/tugas_akhir/{$ta->id}/KRS_" . ($n + 1) . ".pdf",
                        'status_valid' => rand(0, 1),
                    ]);
                    Berkas::create([
                        'berkasable_type' => 'App\Models\TugasAkhir',
                        'berkasable_id' => $ta->id,
                        'nama_berkas' => 'Bukti Persetujuan Pembimbing',
                        'file_path' => "/private/tugas_akhir/{$ta->id}/Persetujuan_Pembimbing_" . ($n + 1) . ".pdf",
                        'status_valid' => rand(0, 1),
                    ]);
                    Berkas::create([
                        'berkasable_type' => 'App\Models\TugasAkhir',
                        'berkasable_id' => $ta->id,
                        'nama_berkas' => 'Transkrip Nilai Mahasiswa',
                        'file_path' => "/private/tugas_akhir/{$ta->id}/Transkrip_Nilai_UjianTA_" . ($n + 1) . ".pdf",
                        'status_valid' => rand(0, 1),
                    ]);
                    Berkas::create([
                        'berkasable_type' => 'App\Models\TugasAkhir',
                        'berkasable_id' => $ta->id,
                        'nama_berkas' => 'Bukti Sertifikat Kompetensi',
                        'file_path' => "/private/tugas_akhir/{$ta->id}/SertifikatKompetensi_" . ($n + 1) . ".pdf",
                        'status_valid' => rand(0, 1),
                    ]);
                    Berkas::create([
                        'berkasable_type' => 'App\Models\TugasAkhir',
                        'berkasable_id' => $ta->id,
                        'nama_berkas' => 'Sertifikat TOEIC',
                        'file_path' => "/private/tugas_akhir/{$ta->id}/TOEIC_" . ($n + 1) . ".pdf",
                        'status_valid' => rand(0, 1),
                    ]);
                    Berkas::create([
                        'berkasable_type' => 'App\Models\TugasAkhir',
                        'berkasable_id' => $ta->id,
                        'nama_berkas' => 'Berita Acara Seminar Hasil',
                        'file_path' => "/private/tugas_akhir/{$ta->id}/BeritaAcaraSemhas_" . ($n + 1) . ".pdf",
                        'status_valid' => rand(0, 1),
                    ]);
                    Berkas::create([
                        'berkasable_type' => 'App\Models\TugasAkhir',
                        'berkasable_id' => $ta->id,
                        'nama_berkas' => 'Surat Perpanjangan Masa Studi',
                        'file_path' => "/private/tugas_akhir/{$ta->id}/SuratPerpanjanganStudi_" . ($n + 1) . ".pdf",
                        'status_valid' => rand(0, 1),
                    ]);

                    // Validasi admin (diterima/ditolak/catatan)
                    $statusValid = rand(0, 1);
                    $ta->status_valid = $statusValid;
                    $ta->catatan_proposal = $statusValid ? null : 'Perbaiki lampiran dan format';
                    $ta->save();

                    // Admin penjadwalan ujian
                    $ujian = UjianTA::create([
                        'tugas_akhir_id' => $ta->id,
                        'tanggal_ujian' => now()->addDays(rand(1, 30)),
                        'ruang_id' => rand(1, 3),
                        'sesi_id' => rand(1, 3),
                        'semester_id' => $semesterId,
                        'tahun_ajaran_id' => $tahunAjaranId,
                        'status_ujian' => 'aktif',
                    ]);

                    // Admin plotting dosen penguji (pivot dosen_penguji)
                    $nilaiUjian1 = NilaiUjianTA::create([
                        'kualitas_tata_laporan' => rand(70, 95),
                        'kemampuan_teknis' => rand(70, 95),
                        'kualitas_produk' => rand(70, 95),
                        'presentasi' => rand(70, 95),
                        'rerata_ujian_ta' => rand(70, 95),
                    ]);
                    $nilaiUjian2 = NilaiUjianTA::create([
                        'kualitas_tata_laporan' => rand(70, 95),
                        'kemampuan_teknis' => rand(70, 95),
                        'kualitas_produk' => rand(70, 95),
                        'presentasi' => rand(70, 95),
                        'rerata_ujian_ta' => rand(70, 95),
                    ]);
                    // Attach ke pivot dosen_penguji
                    $ta->dosenPenguji()->attach($penguji1, [
                        'nilai_ujian_ta_id' => $nilaiUjian1->id,
                        'jabatan_penguji' => 'ketua',
                        'revisi' => rand(0, 1) ? 'Revisi minor' : null,
                        'catatan_semhas' => rand(0, 1) ? 'Catatan semhas penguji 1' : null,
                    ]);
                    $ta->dosenPenguji()->attach($penguji2, [
                        'nilai_ujian_ta_id' => $nilaiUjian2->id,
                        'jabatan_penguji' => 'penguji',
                        'revisi' => rand(0, 1) ? 'Revisi mayor' : null,
                        'catatan_semhas' => rand(0, 1) ? 'Catatan semhas penguji 2' : null,
                    ]);
                }
            }
        }
    }
}