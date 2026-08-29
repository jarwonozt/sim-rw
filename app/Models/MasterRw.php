<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterRw extends Model
{
    /** @use HasFactory<\Database\Factories\MasterRwFactory> */
    use HasFactory;

    protected $table = 'master_rw';

    protected $fillable = [
        'village_id',
        'nomor_rw',
        'ketua_rw_id',
        'address',
    ];

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
