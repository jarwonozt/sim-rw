<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterRtResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'master_rw_id' => $this->master_rw_id,
            'nomor_rt' => $this->nomor_rt,
            'ketua_rt_id' => $this->ketua_rt_id,
            'family_heads_count' => $this->whenCounted('familyHeads'),
            'ketua_rt' => new UserResource($this->whenLoaded('ketuaRt')),
        ];
    }
}
