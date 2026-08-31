<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryLoanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'borrower_name' => $this->borrower_name,
            'borrower_phone' => $this->borrower_phone,
            'quantity_borrowed' => $this->quantity_borrowed,
            'purpose' => $this->purpose,
            'loan_date' => $this->loan_date,
            'due_date' => $this->due_date,
            'return_date' => $this->return_date,
            'returned_condition' => $this->returned_condition,
            'status' => $this->status,
            'is_overdue' => $this->is_overdue ?? null,
            'notes' => $this->notes,
            'item' => new InventoryItemResource($this->whenLoaded('item')),
            'resident' => new ResidentResource($this->whenLoaded('resident')),
            'handled_by' => new UserResource($this->whenLoaded('handledBy')),
        ];
    }
}
