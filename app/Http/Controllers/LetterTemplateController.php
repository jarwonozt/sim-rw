<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLetterTemplateRequest;
use App\Http\Requests\UpdateLetterTemplateRequest;
use App\Models\LetterTemplate;
use App\Services\LetterContentRenderer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LetterTemplateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Letters/Templates/Index', [
            'templates' => LetterTemplate::query()->withCount('letters')->orderBy('name')->get(),
            'placeholders' => LetterContentRenderer::PLACEHOLDERS,
        ]);
    }

    public function store(StoreLetterTemplateRequest $request): RedirectResponse
    {
        LetterTemplate::create($request->validated());

        return to_route('letter-templates.index')->with('success', 'Template surat berhasil ditambahkan.');
    }

    public function update(UpdateLetterTemplateRequest $request, LetterTemplate $letterTemplate): RedirectResponse
    {
        $letterTemplate->update($request->validated());

        return to_route('letter-templates.index')->with('success', 'Template surat berhasil diperbarui.');
    }

    public function destroy(LetterTemplate $letterTemplate): RedirectResponse
    {
        if ($letterTemplate->letters()->exists()) {
            return back()->with('error', 'Template tidak bisa dihapus karena sudah dipakai untuk menerbitkan surat.');
        }

        $letterTemplate->delete();

        return to_route('letter-templates.index')->with('success', 'Template surat berhasil dihapus.');
    }
}
