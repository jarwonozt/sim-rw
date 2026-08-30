<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWhatsappTemplateRequest;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappTemplateRenderer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WhatsappTemplateController extends Controller
{
    /**
     * Event sistem yang bisa dikaitkan ke sebuah template, dengan label
     * tampilan dan placeholder yang berlaku untuk tiap event.
     *
     * @var array<string, array{label: string, placeholders: array<int, string>}>
     */
    private const EVENTS = [
        'complaint_resolved' => [
            'label' => 'Pengaduan Selesai Ditindaklanjuti',
            'placeholders' => WhatsappTemplateRenderer::COMPLAINT_RESOLVED_PLACEHOLDERS,
        ],
    ];

    public function index(): Response
    {
        return Inertia::render('WhatsappTemplates/Index', [
            'templates' => WhatsappTemplate::query()->orderByDesc('created_at')->get(),
            'events' => collect(self::EVENTS)
                ->map(fn ($event, $key) => ['key' => $key, 'label' => $event['label'], 'placeholders' => $event['placeholders']])
                ->values(),
            'broadcastPlaceholders' => WhatsappTemplateRenderer::BROADCAST_PLACEHOLDERS,
        ]);
    }

    public function store(StoreWhatsappTemplateRequest $request): RedirectResponse
    {
        WhatsappTemplate::create($request->validated());

        return to_route('whatsapp-templates.index')->with('success', 'Template berhasil ditambahkan.');
    }

    public function update(StoreWhatsappTemplateRequest $request, WhatsappTemplate $whatsappTemplate): RedirectResponse
    {
        $whatsappTemplate->update($request->validated());

        return to_route('whatsapp-templates.index')->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(WhatsappTemplate $whatsappTemplate): RedirectResponse
    {
        $whatsappTemplate->delete();

        return to_route('whatsapp-templates.index')->with('success', 'Template berhasil dihapus.');
    }
}
