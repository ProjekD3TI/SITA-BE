<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;


class TugasAkhirResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $naskahTA = $this->berkas->where('nama_berkas', 'naskah_ta')->first();
        $krs = $this->berkas->where('nama_berkas', 'krs')->first();
        $persetujuanPembimbing = $this->berkas->where('nama_berkas', 'persetujuan_pembimbing')->first();
        $sertifikatKompetensi = $this->berkas->where('nama_berkas', 'sertifikat_kompetensi')->first();
        $toeic = $this->berkas->where('nama_berkas', 'toeic')->first();
        $beritaAcaraSemhas = $this->berkas->where('nama_berkas', 'berita_acara_semhas')->first();
        $masaPerpanjanganStudi = $this->berkas->where('nama_berkas', 'masa_perpanjangan_studi')->first();
        $fileTranskrip = $this->berkas->where('nama_berkas', 'file_transkrip')->first();

        return [
            'id' => $this->id,
            'mahasiswa_id' => $this->mahasiswa_id,
            'proposal_id' => $this->proposal_id,
            'semester_id' => $this->semester_id,
            'dosen_pembimbing_id' => $this->dosen_pembimbing_id,
            'nilai_bimbingan_id' => $this->nilai_bimbingan,
            'tahun_ajaran_id' => $this->tahun_ajaran_id,
            'judul_tugas_akhir' => $this->judul_tugas_akhir,
            'krs' => $krs ? Storage::url($krs->file_path) : null,
            'persetujuan_pembimbing' => $persetujuanPembimbing ? Storage::url($persetujuanPembimbing->file_path) : null,
            'sertifikat_kompetensi' => $sertifikatKompetensi ? Storage::url($sertifikatKompetensi->file_path) : null,
            'toeic' => $toeic ? Storage::url($toeic->file_path) : null,
            'berita_acara_semhas' => $beritaAcaraSemhas ? Storage::url($beritaAcaraSemhas->file_path) : null,
            'naskah_ta' => $naskahTA ? Storage::url($naskahTA->file_path) : null,
            'masa_perpanjangan_studi' => $masaPerpanjanganStudi ? Storage::url($masaPerpanjanganStudi->file_path) : null,
            'nilai_proposal' => $this->nilai_proposal,
            'nilai_akhir' => $this->nilai_akhir,
            'catatan_proposal' => $this->catatan_proposal,
            'file_transkrip' => $fileTranskrip ? Storage::url($fileTranskrip->file_path) : null,
            'status_kelulusan' => $this->status_kelulusan,
            'status_valid' => $this->status_valid,
            'status_aktif' => $this->status_aktif,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

}
