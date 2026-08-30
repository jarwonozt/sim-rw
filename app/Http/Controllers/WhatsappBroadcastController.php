<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWhatsappBroadcastRequest;
use App\Models\MasterRt;
use App\Models\Resident;
use App\Models\WhatsappBroadcast;
use App\Models\WhatsappTemplate;
use App\Services\FonnteClient;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WhatsappBroadcastController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('WhatsappBroadcast/Index', [
            'rtOptions' => MasterRt::query()->orderBy('nomor_rt')->get(['id', 'nomor_rt']),
            'templates' => WhatsappTemplate::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'content']),
            'broadcasts' => WhatsappBroadcast::query()
                ->with('sender:id,name', 'rt:id,nomor_rt')
                ->latest('created_at')
                ->paginate(10),
        ]);
    }

    public function store(StoreWhatsappBroadcastRequest $request, FonnteClient $fonnte): RedirectResponse
    {
        $rtId = $request->integer('rt_id') ?: null;
        $message = $request->string('message')->toString();

        $phones = Resident::query()
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->when($rtId, fn ($query) => $query->whereHas('familyHead', fn ($query) => $query->where('rt_id', $rtId)))
            ->pluck('phone')
            ->unique();

        if ($phones->isEmpty()) {
            return back()->with('error', 'Tidak ada penduduk dengan nomor HP pada target yang dipilih.');
        }

        $successCount = 0;

        foreach ($phones as $phone) {
            if ($fonnte->sendMessage($phone, $message)) {
                $successCount++;
            }
        }

        $broadcast = WhatsappBroadcast::create([
            'sent_by' => $request->user()->id,
            'rt_id' => $rtId,
            'message' => $message,
            'recipients_count' => $phones->count(),
            'success_count' => $successCount,
            'failed_count' => $phones->count() - $successCount,
        ]);

        ActivityLogger::log(
            'whatsapp.broadcast_sent',
            "Mengirim broadcast WhatsApp ke {$broadcast->recipients_count} penduduk ({$successCount} berhasil)."
        );

        return to_route('whatsapp-broadcast.index')->with(
            'success',
            "Broadcast selesai: {$successCount} dari {$broadcast->recipients_count} pesan berhasil dikirim."
        );
    }
}
