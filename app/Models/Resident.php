<?php

namespace App\Models;

use App\Models\Scopes\RtOwnedThroughFamilyHeadScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ScopedBy([RtOwnedThroughFamilyHeadScope::class])]
class Resident extends Model
{
    /** @use HasFactory<\Database\Factories\ResidentFactory> */
    use HasFactory;

    protected $fillable = [
        'family_head_id',
        'nik',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'is_family_head',
        'relationship_status',
        'occupation',
        'religion',
        'education',
        'marital_status',
        'phone',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_family_head' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<FamilyHead, $this>
     */
    public function familyHead(): BelongsTo
    {
        return $this->belongsTo(FamilyHead::class);
    }

    /**
     * @return HasOne<User, $this>
     */
    public function userAccount(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * @return HasMany<Letter, $this>
     */
    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class);
    }

    /**
     * @return HasMany<PatrolSchedule, $this>
     */
    public function patrolSchedules(): HasMany
    {
        return $this->hasMany(PatrolSchedule::class);
    }
}
