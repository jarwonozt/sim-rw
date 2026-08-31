<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InventoryItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'available_quantity' => $this->available_quantity ?? null,
            'condition' => $this->condition,
            'location' => $this->location,
            'photo_url' => $this->photo ? Storage::disk('public')->url($this->photo) : null,
            'notes' => $this->notes,
            'category' => new InventoryCategoryResource($this->whenLoaded('category')),
            'rt' => new MasterRtResource($this->whenLoaded('rt')),
        ];
    }
}
