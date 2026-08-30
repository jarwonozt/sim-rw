<?php

namespace App\Notifications;

use App\Models\Complaint;
use App\Models\WhatsappTemplate;
use App\Notifications\Channels\FonnteChannel;
use App\Services\WhatsappTemplateRenderer;
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
     * Email selalu dikirim (FR05.3, minimal requirement). WhatsApp lewat
     * Fonnte ikut terkirim bila akun warga terhubung ke data Penduduk yang
     * punya nomor telepon — FonnteChannel otomatis melewati bila tidak ada.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', FonnteChannel::class];
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

    /**
     * Get the WhatsApp (Fonnte) representation of the notification.
     *
     * Memakai template aktif "complaint_resolved" bila pengurus RW sudah
     * mengatur satu lewat menu Template Notifikasi WhatsApp; bila belum ada,
     * jatuh ke pesan bawaan supaya notifikasi tetap terkirim.
     */
    public function toFonnte(object $notifiable): string
    {
        $template = WhatsappTemplate::query()
            ->where('event_key', 'complaint_resolved')
            ->where('is_active', true)
            ->first();

        $values = [
            'nama_warga' => $notifiable->name,
            'judul_pengaduan' => $this->complaint->title,
        ];

        if ($template) {
            return app(WhatsappTemplateRenderer::class)->render($template->content, $values);
        }

        return "Halo {$values['nama_warga']}, pengaduan Anda \"{$values['judul_pengaduan']}\" telah *selesai* ditindaklanjuti oleh pengurus RW. Terima kasih atas partisipasi Anda menjaga lingkungan RW.";
    }
}
