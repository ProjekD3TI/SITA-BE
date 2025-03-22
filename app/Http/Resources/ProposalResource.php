<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
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
            'judul_proposal' => $this->judul_proposal,
            'file_proposal' => $this->file_proposal,
            'file_transkrip' => $this->file_transkrip,
            'status_proposal' => $this->status_proposal,
            'tanggal_unggah' => $this->tanggal_unggah,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
