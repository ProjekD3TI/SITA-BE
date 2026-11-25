<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BerkasResource extends JsonResource
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
            'berkasable_type' => $this->parent_type,
            'berkasable_id' => $this->parent_id,
            'nama_berkas' => $this->nama_berkas,
            'file_path' => $this->file_path,
            'status_valid' => $this->status_valid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
