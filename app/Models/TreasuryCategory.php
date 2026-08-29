<?php

namespace App\Models;

use Database\Factories\TreasuryCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type'])]
class TreasuryCategory extends Model
{
    /** @use HasFactory<TreasuryCategoryFactory> */
    use HasFactory;

    /**
     * @return HasMany<Treasury, $this>
     */
    public function treasuries(): HasMany
    {
        return $this->hasMany(Treasury::class);
    }
}
