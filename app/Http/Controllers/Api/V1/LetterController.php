<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLetterRequest;
use App\Http\Resources\LetterResource;
use App\Models\Letter;
use App\Services\LetterContentRenderer;
use App\Services\LetterNumberGenerator;
use App\Support\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;

class LetterController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return response()->json(LetterResource::collection($letters)->response()->getData(true));
    }

    public function store(StoreLetterRequest $request, LetterNumberGenerator $numberGenerator): JsonResponse
    {
        $issuedDate = Carbon::today();

        $letter = Letter::create([
            ...$request->validated(),
            'letter_number' => $numberGenerator->generate($issuedDate),
            'issued_by' => $request->user()->id,
            'issued_date' => $issuedDate,
        ]);

        ActivityLogger::log('letter.issued', "Menerbitkan surat {$letter->letter_number}.");

        return response()->json(['data' => new LetterResource($letter)], 201);
    }

    public function show(Letter $letter): JsonResponse
    {
        $letter->load('resident:id,name,nik', 'template:id,name', 'issuer:id,name');

        return response()->json(['data' => new LetterResource($letter)]);
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
