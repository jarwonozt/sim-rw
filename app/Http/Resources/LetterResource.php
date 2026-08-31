<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LetterResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'letter_number' => $this->letter_number,
            'purpose' => $this->purpose,
            'issued_date' => $this->issued_date,
            'download_url' => route('api.v1.letters.download', $this->id),
            'resident' => new ResidentResource($this->whenLoaded('resident')),
            'template' => new LetterTemplateResource($this->whenLoaded('template')),
            'issuer' => new UserResource($this->whenLoaded('issuer')),
        ];
    }
}
