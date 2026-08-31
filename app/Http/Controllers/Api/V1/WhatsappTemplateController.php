<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWhatsappTemplateRequest;
use App\Http\Resources\WhatsappTemplateResource;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappTemplateRenderer;
use Illuminate\Http\JsonResponse;

class WhatsappTemplateController extends Controller
{
    /**
     * @var array<string, array{label: string, placeholders: array<int, string>}>
     */
    private const EVENTS = [
        'complaint_resolved' => [
            'label' => 'Pengaduan Selesai Ditindaklanjuti',
            'placeholders' => WhatsappTemplateRenderer::COMPLAINT_RESOLVED_PLACEHOLDERS,
        ],
    ];

    public function index(): JsonResponse
    {
        $templates = WhatsappTemplate::query()->orderByDesc('created_at')->get();

        return response()->json([
            'data' => WhatsappTemplateResource::collection($templates),
            'meta' => [
                'events' => collect(self::EVENTS)
                    ->map(fn ($event, $key) => ['key' => $key, 'label' => $event['label'], 'placeholders' => $event['placeholders']])
                    ->values(),
                'broadcast_placeholders' => WhatsappTemplateRenderer::BROADCAST_PLACEHOLDERS,
            ],
        ]);
    }

    public function store(StoreWhatsappTemplateRequest $request): JsonResponse
    {
        $template = WhatsappTemplate::create($request->validated());

        return response()->json(['data' => new WhatsappTemplateResource($template)], 201);
    }

    public function update(StoreWhatsappTemplateRequest $request, WhatsappTemplate $whatsappTemplate): JsonResponse
    {
        $whatsappTemplate->update($request->validated());

        return response()->json(['data' => new WhatsappTemplateResource($whatsappTemplate)]);
    }

    public function destroy(WhatsappTemplate $whatsappTemplate): JsonResponse
    {
        $whatsappTemplate->delete();

        return response()->json(['message' => 'Template berhasil dihapus.']);
    }
}
