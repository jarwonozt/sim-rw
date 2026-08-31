<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterRwResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'village_id' => $this->village_id,
            'nomor_rw' => $this->nomor_rw,
            'address' => $this->address,
            'ketua_rw_id' => $this->ketua_rw_id,
            'ketua_rw' => new UserResource($this->whenLoaded('ketuaRw')),
            'village' => $this->whenLoaded('village'),
        ];
    }
}
