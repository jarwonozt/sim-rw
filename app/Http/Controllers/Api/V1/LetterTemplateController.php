<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLetterTemplateRequest;
use App\Http\Requests\UpdateLetterTemplateRequest;
use App\Http\Resources\LetterTemplateResource;
use App\Models\LetterTemplate;
use App\Services\LetterContentRenderer;
use Illuminate\Http\JsonResponse;

class LetterTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $templates = LetterTemplate::query()->withCount('letters')->orderBy('name')->get();

        return response()->json([
            'data' => LetterTemplateResource::collection($templates),
            'meta' => ['placeholders' => LetterContentRenderer::PLACEHOLDERS],
        ]);
    }

    public function store(StoreLetterTemplateRequest $request): JsonResponse
    {
        $template = LetterTemplate::create($request->validated());

        return response()->json(['data' => new LetterTemplateResource($template)], 201);
    }

    public function update(UpdateLetterTemplateRequest $request, LetterTemplate $letterTemplate): JsonResponse
    {
        $letterTemplate->update($request->validated());

        return response()->json(['data' => new LetterTemplateResource($letterTemplate)]);
    }

    public function destroy(LetterTemplate $letterTemplate): JsonResponse
    {
        if ($letterTemplate->letters()->exists()) {
            return response()->json([
                'message' => 'Template tidak bisa dihapus karena sudah dipakai untuk menerbitkan surat.',
            ], 422);
        }

        $letterTemplate->delete();

        return response()->json(['message' => 'Template surat berhasil dihapus.']);
    }
}
