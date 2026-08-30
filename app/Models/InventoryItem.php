<?php

namespace App\Models;

use App\Models\Scopes\RtOwnedScope;
use Database\Factories\InventoryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([RtOwnedScope::class])]
#[Fillable([
    'inventory_category_id',
    'rt_id',
    'code',
    'name',
    'quantity',
    'condition',
    'location',
    'photo',
    'notes',
    'created_by',
])]
class InventoryItem extends Model
{
    /** @use HasFactory<InventoryItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<InventoryCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    /**
     * @return BelongsTo<MasterRt, $this>
     */
    public function rt(): BelongsTo
    {
        return $this->belongsTo(MasterRt::class, 'rt_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<InventoryLoan, $this>
     */
    public function loans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class);
    }
}
