<?php

namespace App\Models;

use Database\Factories\TreasuryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'treasury_category_id',
    'type',
    'amount',
    'description',
    'proof_photo',
    'transaction_date',
    'created_by',
])]
class Treasury extends Model
{
    /** @use HasFactory<TreasuryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<TreasuryCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TreasuryCategory::class, 'treasury_category_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
