<?php

namespace App\Services;

/**
 * Mengganti placeholder [key] pada template pesan WhatsApp dengan nilai
 * sebenarnya. Dipakai baik oleh notifikasi otomatis (mis. pengaduan selesai)
 * maupun saat broadcast manual.
 */
class WhatsappTemplateRenderer
{
    /**
     * Placeholder yang didukung untuk template event "complaint_resolved".
     *
     * @var array<int, string>
     */
    public const COMPLAINT_RESOLVED_PLACEHOLDERS = ['nama_warga', 'judul_pengaduan'];

    /**
     * Placeholder yang didukung untuk template umum/broadcast.
     *
     * @var array<int, string>
     */
    public const BROADCAST_PLACEHOLDERS = ['nama_warga', 'nomor_rt', 'nomor_rw'];

    /**
     * @param  array<string, string>  $values
     */
    public function render(string $content, array $values): string
    {
        foreach ($values as $placeholder => $value) {
            $content = str_replace("[{$placeholder}]", $value, $content);
        }

        return $content;
    }
}
