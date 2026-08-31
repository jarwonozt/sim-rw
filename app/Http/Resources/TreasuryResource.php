<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TreasuryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'proof_photo_url' => $this->proof_photo ? Storage::disk('public')->url($this->proof_photo) : null,
            'transaction_date' => $this->transaction_date,
            'category' => new TreasuryCategoryResource($this->whenLoaded('category')),
            'recorded_by' => new UserResource($this->whenLoaded('recordedBy')),
        ];
    }
}
