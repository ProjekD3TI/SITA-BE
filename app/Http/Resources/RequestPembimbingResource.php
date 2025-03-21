<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestPembimbingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'tugas_akhir_id' => $this->tugas_akhir_id,
            'dosen_id' => $this->dosen_id,
            'status_request' => $this->status_request,
            'tanggal_request' => $this->tanggal_request,
            'tanggal_respon' => $this->tanggal_respon,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
