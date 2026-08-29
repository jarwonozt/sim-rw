<?php

namespace App\Models;

use Database\Factories\MasterRwFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['village_id', 'nomor_rw', 'ketua_rw_id', 'address'])]
class MasterRw extends Model
{
    /** @use HasFactory<MasterRwFactory> */
    use HasFactory;

    protected $table = 'master_rw';

    /**
     * @return BelongsTo<Village, $this>
     */
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function ketuaRw(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_rw_id');
    }

    /**
     * @return HasMany<MasterRt, $this>
     */
    public function rts(): HasMany
    {
        return $this->hasMany(MasterRt::class);
    }
}
