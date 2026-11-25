<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NilaiUjianTAResource extends JsonResource
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
            'rerata_ujian_ta' => $this->rerata_ujian_ta,
            'kualitas_tata_laporan' => $this->kualitas_tata_laporan,
            'kemampuan_teknis' => $this->kemampuan_teknis,
            'kualitas_produk' => $this->kualitas_produk,
            'presentasi' => $this->presentasi,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
