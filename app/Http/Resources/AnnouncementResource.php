<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AnnouncementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'image_url' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'publish_date' => $this->publish_date,
            'expire_date' => $this->expire_date,
            'author' => new UserResource($this->whenLoaded('author')),
        ];
    }
}
