<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $event->user->forceFill(['last_login_at' => now()])->save();

        ActivityLog::create([
            'user_id' => $event->user->id,
            'action' => 'login',
            'description' => "{$event->user->name} login ke sistem.",
            'ip_address' => Request::ip(),
        ]);
    }
}
