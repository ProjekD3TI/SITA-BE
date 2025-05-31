<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
        Carbon::setLocale('id');
        return [
            'id' => $this->id,
            'tugas_akhir_id' => $this->tugas_akhir_id,
            'catatan' => $this->catatan,
            'bab' => $this->bab,
            'status_verifikasi' => $this->status_verifikasi,
            'tanggal_bimbingan' => $this->tanggal_bimbingan
                ? Carbon::parse($this->tanggal_bimbingan)->translatedFormat('l, d F Y')
                : null,
        ];
    }
}
