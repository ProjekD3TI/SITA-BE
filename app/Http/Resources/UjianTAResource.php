<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UjianTAResource extends JsonResource
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
            'tugas_akhir_id' => $this->tugas_akhir_id,
            'tanggal_ujian' => $this->tanggal_ujian,
            'ruang_id' => $this->ruang_id,
            'sesi_id' => $this->sesi_id,
            'semester_id' => $this->semester_id,
            'tahun_ajaran_id' => $this->tahun_ajaran_id,
            'status_ujian' => $this->status_ujian,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
