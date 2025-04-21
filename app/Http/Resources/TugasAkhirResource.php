<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TugasAkhirResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mahasiswa_id' => $this->mahasiswa_id,
            'proposal_id' => $this->proposal_id,
            'semester_id' => $this->semester_id,
            'dosen_pembimbing_id' => $this->dosen_pembimbing_id,
            'nilai_bimbingan_id' => $this->nilai_bimbingan,
            'tahun_ajaran_id' => $this->tahun_ajaran_id,
            'judul_tugas_akhir' => $this->judul_tugas_akhir,
            'krs' => $this->krs,
            'persetujuan_pembimbing' => $this->persetujuan_pembimbing,
            'sertifikat_kompetensi' => $this->sertifikat_kompetensi,
            'toeic' => $this->toeic,
            'berita_acara_semhas' => $this->berita_acara_semhas,
            'naskah_ta' => $this->naskah_ta,
            'masa_perpanjangan_studi' => $this->masa_perpanjangan_studi,
            'nilai_proposal' => $this->nilai_proposal,
            'nilai_akhir' => $this->nilai_akhir,
            'catatan_proposal' => $this->catatan_proposal,
            'file_transkrip' => $this->file_transkrip,
            'status_valid' => $this->status_valid,
            'status_aktif' => $this->status_aktif,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
