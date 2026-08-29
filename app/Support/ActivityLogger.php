<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Pencatat singkat untuk Activity Log (audit trail, PRD Bagian 6.3) dari
 * dalam controller, tanpa perlu mengulang boilerplate user/IP di tiap tempat.
 */
class ActivityLogger
{
    public static function log(string $action, string $description): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip(),
        ]);
    }
}
