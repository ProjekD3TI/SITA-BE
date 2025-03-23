<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DosenPengujiResource extends JsonResource
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
            'dosen_id' => $this->dosen_id,
            'tugas_akhir_id' => $this->tugas_akhir_id,
            'revisi' => $this->revisi,
            'catatan_semhas' => $this->catatan_semhas,
            'nilai_ujian_ta' => $this->nilai_ujian_ta,
        ];
    }
}