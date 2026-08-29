<?php

namespace App\Models;

use Database\Factories\SubdistrictFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'district_id', 'name'])]
class Subdistrict extends Model
{
    /** @use HasFactory<SubdistrictFactory> */
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @return BelongsTo<District, $this>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * @return HasMany<Village, $this>
     */
    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }
}
