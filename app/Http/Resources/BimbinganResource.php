<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BimbinganResource extends JsonResource
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
            'catatan' => $this->catatan,
            'status_verifikasi' => $this->status_verifikasi,
            'tanggal_bimbingan' => $this->tanggal_bimbingan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
