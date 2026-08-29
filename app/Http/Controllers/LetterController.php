<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLetterRequest;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Resident;
use App\Services\LetterContentRenderer;
use App\Services\LetterNumberGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class LetterController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $letters = Letter::query()
            ->with(['resident:id,name,nik', 'template:id,name,type'])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('resident', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            }))
            ->orderByDesc('issued_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Letters/Index', [
            'letters' => $letters,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(Request $request): Response
    {
        $resident = null;

        if ($request->filled('resident_id')) {
            $resident = Resident::query()->find($request->integer('resident_id'), ['id', 'name', 'nik']);
        }

        return Inertia::render('Letters/Create', [
            'templates' => LetterTemplate::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']),
            'preselectedResident' => $resident,
        ]);
    }

    public function store(StoreLetterRequest $request, LetterNumberGenerator $numberGenerator): RedirectResponse
    {
        $issuedDate = Carbon::today();

        $letter = Letter::create([
            ...$request->validated(),
            'letter_number' => $numberGenerator->generate($issuedDate),
            'issued_by' => $request->user()->id,
            'issued_date' => $issuedDate,
        ]);

        return to_route('letters.show', $letter)->with('success', 'Surat berhasil diterbitkan.');
    }

    public function show(Letter $letter): Response
    {
        $letter->load('resident:id,name,nik', 'template:id,name', 'issuer:id,name');

        return Inertia::render('Letters/Show', [
            'letter' => $letter,
        ]);
    }

    public function download(Letter $letter, LetterContentRenderer $renderer): HttpResponse
    {
        $letter->load('resident.familyHead.rt.rw.village', 'resident.familyHead.rt.rw.ketuaRw', 'resident.familyHead.residents', 'template', 'issuer');

        $safeNumber = str_replace('/', '-', $letter->letter_number);

        return Pdf::loadView('letters.pdf', [
            'letter' => $letter,
            'body' => $renderer->render($letter),
            'rw' => $letter->resident->familyHead->rt->rw,
        ])->download("surat-{$safeNumber}.pdf");
    }
}
