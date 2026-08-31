<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsappBroadcastResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'recipients_count' => $this->recipients_count,
            'success_count' => $this->success_count,
            'failed_count' => $this->failed_count,
            'rt' => new MasterRtResource($this->whenLoaded('rt')),
            'sender' => new UserResource($this->whenLoaded('sender')),
            'created_at' => $this->created_at,
        ];
    }
}
