<?php

namespace App\Models;

use App\Models\Scopes\RtOwnedScope;
use Database\Factories\FamilyHeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([RtOwnedScope::class])]
#[Fillable(['rt_id', 'no_kk', 'address', 'postal_code'])]
class FamilyHead extends Model
{
    /** @use HasFactory<FamilyHeadFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<MasterRt, $this>
     */
    public function rt(): BelongsTo
    {
        return $this->belongsTo(MasterRt::class, 'rt_id');
    }

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }
}
