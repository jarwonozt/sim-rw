<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFamilyHeadRequest;
use App\Http\Requests\UpdateFamilyHeadRequest;
use App\Http\Resources\FamilyHeadResource;
use App\Models\FamilyHead;
use App\Models\MasterRt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FamilyHeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString();

        $familyHeads = FamilyHead::query()
            ->with('rt:id,nomor_rt,master_rw_id')
            ->withCount('residents')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('no_kk', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            }))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return response()->json(FamilyHeadResource::collection($familyHeads)->response()->getData(true));
    }

    public function store(StoreFamilyHeadRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['rt_id'] = $this->resolveRtId($request, $data['rt_id']);

        $familyHead = FamilyHead::create($data);

        return response()->json(['data' => new FamilyHeadResource($familyHead)], 201);
    }

    public function show(FamilyHead $familyHead): JsonResponse
    {
        $familyHead->load('rt:id,nomor_rt', 'residents');

        return response()->json(['data' => new FamilyHeadResource($familyHead)]);
    }

    public function update(UpdateFamilyHeadRequest $request, FamilyHead $familyHead): JsonResponse
    {
        $data = $request->validated();
        $data['rt_id'] = $this->resolveRtId($request, $data['rt_id']);

        $familyHead->update($data);

        return response()->json(['data' => new FamilyHeadResource($familyHead)]);
    }

    public function destroy(FamilyHead $familyHead): JsonResponse
    {
        if ($familyHead->residents()->exists()) {
            return response()->json([
                'message' => 'KK tidak bisa dihapus karena masih memiliki data penduduk.',
            ], 422);
        }

        $familyHead->delete();

        return response()->json(['message' => 'Data KK berhasil dihapus.']);
    }

    private function resolveRtId(Request $request, int $submittedRtId): int
    {
        $user = $request->user();

        if ($user->role !== 'ketua_rt') {
            return $submittedRtId;
        }

        return MasterRt::query()->where('ketua_rt_id', $user->id)->value('id') ?? abort(403);
    }
}
