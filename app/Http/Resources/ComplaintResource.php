<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ComplaintResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'photo_url' => $this->photo ? Storage::disk('public')->url($this->photo) : null,
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
            'rt' => new MasterRtResource($this->whenLoaded('rt')),
            'logs' => ComplaintLogResource::collection($this->whenLoaded('logs')),
            'created_at' => $this->created_at,
        ];
    }
}
