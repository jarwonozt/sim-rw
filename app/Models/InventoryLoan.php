<?php

namespace App\Models;

use App\Models\Scopes\RtOwnedThroughInventoryItemScope;
use Database\Factories\InventoryLoanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([RtOwnedThroughInventoryItemScope::class])]
#[Fillable([
    'inventory_item_id',
    'resident_id',
    'borrower_name',
    'borrower_phone',
    'quantity_borrowed',
    'purpose',
    'loan_date',
    'due_date',
    'return_date',
    'returned_condition',
    'status',
    'handled_by',
    'notes',
])]
class InventoryLoan extends Model
{
    /** @use HasFactory<InventoryLoanFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<InventoryItem, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
