<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMasterRtRequest;
use App\Http\Requests\UpdateMasterRtRequest;
use App\Http\Resources\MasterRtResource;
use App\Models\MasterRt;
use App\Models\MasterRw;
use Illuminate\Http\JsonResponse;

class MasterRtController extends Controller
{
    public function index(): JsonResponse
    {
        $rts = MasterRt::query()
            ->withCount('familyHeads')
            ->with('ketuaRt:id,name')
            ->orderBy('nomor_rt')
            ->get();

        return response()->json(['data' => MasterRtResource::collection($rts)]);
    }

    public function store(StoreMasterRtRequest $request): JsonResponse
    {
        $rw = MasterRw::query()->firstOrFail();

        $rt = $rw->rts()->create($request->validated());

        return response()->json(['data' => new MasterRtResource($rt)], 201);
    }

    public function update(UpdateMasterRtRequest $request, MasterRt $rt): JsonResponse
    {
        $rt->update($request->validated());

        return response()->json(['data' => new MasterRtResource($rt)]);
    }

    public function destroy(MasterRt $rt): JsonResponse
    {
        if ($rt->familyHeads()->exists()) {
            return response()->json([
                'message' => 'RT tidak bisa dihapus karena masih memiliki data KK.',
            ], 422);
        }

        $rt->delete();

        return response()->json(['message' => 'RT berhasil dihapus.']);
    }
}
