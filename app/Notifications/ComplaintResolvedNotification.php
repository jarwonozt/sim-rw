<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintResolvedNotification extends Notification
{
    use Queueable;

    public function __construct(public Complaint $complaint) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengaduan Anda Telah Selesai Ditindaklanjuti')
            ->greeting("Halo {$notifiable->name},")
            ->line("Pengaduan Anda \"{$this->complaint->title}\" telah selesai ditindaklanjuti oleh pengurus RW.")
            ->line('Terima kasih atas partisipasi Anda dalam menjaga lingkungan RW.');
    }
}
