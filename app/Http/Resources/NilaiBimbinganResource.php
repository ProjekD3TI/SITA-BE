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
            'rerata_bimbingan' => $this->rerata_bimbingan,
            'disiplin' => $this->disiplin,
            'komunikasi' => $this->komunikasi,
            'skill_pengetahuan' => $this->skill_pengetahuan,
            'tingkah_laku' => $this->tingkah_laku,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}