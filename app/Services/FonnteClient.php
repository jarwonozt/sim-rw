<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien tipis untuk WhatsApp Gateway Fonnte (https://fonnte.com), dipakai
 * oleh FonnteChannel untuk notifikasi WhatsApp (FR05.3).
 *
 * Kegagalan pengiriman (token belum diisi, nomor tidak valid, API down)
 * sengaja tidak melempar exception — cukup dicatat di log — supaya tidak
 * menggagalkan alur utama (mis. update status pengaduan) hanya karena
 * WhatsApp gagal terkirim.
 */
class FonnteClient
{
    public function sendMessage(string $phone, string $message): bool
    {
        $token = config('services.fonnte.token');

        if (! $token) {
            Log::warning('Fonnte: FONNTE_TOKEN belum dikonfigurasi, notifikasi WhatsApp dilewati.');

            return false;
        }

        $target = $this->normalizePhone($phone);

        if (! $target) {
            Log::warning("Fonnte: nomor telepon tidak valid, dilewati: {$phone}");

            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->asForm()
                ->timeout(10)
                ->post(config('services.fonnte.endpoint'), [
                    'target' => $target,
                    'message' => $message,
                ]);
        } catch (ConnectionException $e) {
            Log::error("Fonnte: tidak dapat menghubungi API — {$e->getMessage()}");

            return false;
        }

        if ($response->failed()) {
            Log::error('Fonnte: API mengembalikan error.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Menormalkan nomor lokal (0812..., +62812..., 62812...) ke format
     * yang diharapkan Fonnte: 62812... tanpa spasi/simbol.
     */
    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62'.$digits;
        }

        return strlen($digits) >= 9 ? $digits : null;
    }
}
