<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProposalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fileProposal = $this->berkas->where('nama_berkas', 'file_proposal')->first();
        $fileTranskrip = $this->berkas->where('nama_berkas', 'file_transkrip')->first();

        return [
            'id' => $this->id,
            'judul_proposal' => $this->judul_proposal,
            'file_proposal' => $fileProposal ? Storage::url($fileProposal->file_path) : null,
            'file_transkrip' => $fileTranskrip ? Storage::url($fileTranskrip->file_path) : null,
            'status_proposal' => $this->status_proposal,
            'catatan_perbaikan' => $this->catatan_perbaikan,
            'tanggal_unggah' => $this->tanggal_unggah,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
