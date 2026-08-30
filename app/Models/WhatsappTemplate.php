<?php

namespace App\Models;

use Database\Factories\WhatsappTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Template pesan WhatsApp. `event_key` terisi ("complaint_resolved", dst)
 * berarti template ini otomatis dipakai sistem untuk notifikasi terkait;
 * `event_key` null berarti template umum yang cuma dipilih manual saat
 * broadcast (lihat WhatsappBroadcastController).
 */
#[Fillable(['name', 'event_key', 'content', 'is_active'])]
class WhatsappTemplate extends Model
{
    /** @use HasFactory<WhatsappTemplateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
