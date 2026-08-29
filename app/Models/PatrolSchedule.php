<?php

namespace App\Models;

use Database\Factories\PatrolScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rt_id', 'resident_id', 'schedule_date', 'shift', 'status'])]
class PatrolSchedule extends Model
{
    /** @use HasFactory<PatrolScheduleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<MasterRt, $this>
     */
    public function rt(): BelongsTo
    {
        return $this->belongsTo(MasterRt::class, 'rt_id');
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }
}
