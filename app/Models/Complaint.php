<?php

namespace App\Models;

use App\Models\Scopes\RtOwnedScope;
use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([RtOwnedScope::class])]
#[Fillable(['user_id', 'rt_id', 'title', 'description', 'photo', 'status'])]
class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<MasterRt, $this>
     */
    public function rt(): BelongsTo
    {
        return $this->belongsTo(MasterRt::class, 'rt_id');
    }

    /**
     * @return HasMany<ComplaintLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ComplaintLog::class);
    }
}
