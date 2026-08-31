<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ResidentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'family_head_id' => $this->family_head_id,
            'nik' => $this->nik,
            'name' => $this->name,
            'gender' => $this->gender,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'is_family_head' => $this->is_family_head,
            'relationship_status' => $this->relationship_status,
            'occupation' => $this->occupation,
            'religion' => $this->religion,
            'education' => $this->education,
            'marital_status' => $this->marital_status,
            'phone' => $this->phone,
            'photo_url' => $this->photo ? Storage::disk('public')->url($this->photo) : null,
            'family_head' => new FamilyHeadResource($this->whenLoaded('familyHead')),
        ];
    }
}
