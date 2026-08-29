<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterRt extends Model
{
    /** @use HasFactory<\Database\Factories\MasterRtFactory> */
    use HasFactory;

    protected $table = 'master_rt';

    protected $fillable = [
        'master_rw_id',
        'nomor_rt',
        'ketua_rt_id',
    ];

    /**
     * @return BelongsTo<MasterRw, $this>
     */
    public function rw(): BelongsTo
    {
        return $this->belongsTo(MasterRw::class, 'master_rw_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function ketuaRt(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_rt_id');
    }

    /**
     * @return HasMany<FamilyHead, $this>
     */
    public function familyHeads(): HasMany
    {
        return $this->hasMany(FamilyHead::class, 'rt_id');
    }

    /**
     * @return HasMany<Complaint, $this>
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'rt_id');
    }

    /**
     * @return HasMany<PatrolSchedule, $this>
     */
    public function patrolSchedules(): HasMany
    {
        return $this->hasMany(PatrolSchedule::class, 'rt_id');
    }
}
