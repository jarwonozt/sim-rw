<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyHeadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rt_id' => $this->rt_id,
            'no_kk' => $this->no_kk,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'residents_count' => $this->whenCounted('residents'),
            'rt' => new MasterRtResource($this->whenLoaded('rt')),
            'residents' => ResidentResource::collection($this->whenLoaded('residents')),
        ];
    }
}
