<?php

namespace App\Notifications\Channels;

use App\Services\FonnteClient;
use Illuminate\Notifications\Notification;

/**
 * Channel notifikasi kustom untuk WhatsApp via Fonnte. Notifiable harus
 * mengimplementasikan routeNotificationForFonnte(), dan notifikasi harus
 * mengimplementasikan toFonnte() — mengikuti konvensi channel bawaan
 * Laravel (mail/database/dst).
 */
class FonnteChannel
{
    public function __construct(private readonly FonnteClient $fonnte) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFonnte')) {
            return;
        }

        $phone = method_exists($notifiable, 'routeNotificationForFonnte')
            ? $notifiable->routeNotificationForFonnte($notification)
            : null;

        if (! $phone) {
            return;
        }

        $this->fonnte->sendMessage($phone, $notification->toFonnte($notifiable));
    }
}
