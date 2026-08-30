<?php

namespace App\Models;

use Database\Factories\WhatsappBroadcastFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sent_by', 'rt_id', 'message', 'recipients_count', 'success_count', 'failed_count'])]
class WhatsappBroadcast extends Model
{
    /** @use HasFactory<WhatsappBroadcastFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    /**
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * @return BelongsTo<MasterRt, $this>
     */
    public function rt(): BelongsTo
    {
        return $this->belongsTo(MasterRt::class, 'rt_id');
    }
}
