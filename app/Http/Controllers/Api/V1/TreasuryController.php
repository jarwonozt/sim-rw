<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTreasuryRequest;
use App\Http\Requests\UpdateTreasuryRequest;
use App\Http\Resources\TreasuryResource;
use App\Models\Treasury;
use App\Models\TreasuryCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TreasuryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $month = $request->integer('month') ?: now()->month;
        $year = $request->integer('year') ?: now()->year;
        $type = $request->string('type')->toString();

        $treasuries = Treasury::query()
            ->with('category:id,name,type', 'recordedBy:id,name')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return response()->json(TreasuryResource::collection($treasuries)->response()->getData(true));
    }

    public function store(StoreTreasuryRequest $request): JsonResponse
    {
        $category = TreasuryCategory::query()->findOrFail($request->integer('treasury_category_id'));

        $treasury = Treasury::create([
            ...$request->validated(),
            'type' => $category->type,
            'proof_photo' => $request->file('proof_photo')->store('treasuries', 'public'),
            'created_by' => $request->user()->id,
        ]);

        $verb = $treasury->type === 'in' ? 'Mencatat kas masuk' : 'Mencatat kas keluar';
        ActivityLogger::log('treasury.created', "{$verb} sebesar Rp".number_format((float) $treasury->amount, 0, ',', '.').'.');

        return response()->json(['data' => new TreasuryResource($treasury)], 201);
    }

    public function update(UpdateTreasuryRequest $request, Treasury $treasury): JsonResponse
    {
        $category = TreasuryCategory::query()->findOrFail($request->integer('treasury_category_id'));

        $data = [
            ...$request->validated(),
            'type' => $category->type,
        ];

        if ($request->hasFile('proof_photo')) {
            Storage::disk('public')->delete($treasury->proof_photo);
            $data['proof_photo'] = $request->file('proof_photo')->store('treasuries', 'public');
        }

        $treasury->update($data);

        return response()->json(['data' => new TreasuryResource($treasury)]);
    }

    public function destroy(Treasury $treasury): JsonResponse
    {
        Storage::disk('public')->delete($treasury->proof_photo);
        $treasury->delete();

        return response()->json(['message' => 'Transaksi kas berhasil dihapus.']);
    }
}
