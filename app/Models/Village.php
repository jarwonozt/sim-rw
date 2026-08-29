<?php

namespace App\Models;

use Database\Factories\VillageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'subdistrict_id', 'name'])]
class Village extends Model
{
    /** @use HasFactory<VillageFactory> */
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @return BelongsTo<Subdistrict, $this>
     */
    public function subdistrict(): BelongsTo
    {
        return $this->belongsTo(Subdistrict::class);
    }

    /**
     * @return HasMany<MasterRw, $this>
     */
    public function rws(): HasMany
    {
        return $this->hasMany(MasterRw::class, 'village_id');
    }
}
