<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NilaiBimbinganResource extends JsonResource
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
            'nilai_akhir_bimbingan' => $this->nilai_akhir_bimbingan,
            'nilai_disiplin' => $this->nilai_disiplin,
            'nilai_komunikasi' => $this->nilai_komunikasi,
            'nilai_skill_pengetahuan' => $this->nilai_skill_pengetahuan,
            'nilai_tingkah_laku' => $this->nilai_tingkah_laku,
            'catatan_akhir_bimbingan' => $this->catatan_akhir_bimbingan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
