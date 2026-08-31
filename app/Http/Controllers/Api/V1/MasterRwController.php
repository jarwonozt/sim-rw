<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMasterRwRequest;
use App\Http\Resources\MasterRwResource;
use App\Models\MasterRw;
use Illuminate\Http\JsonResponse;

class MasterRwController extends Controller
{
    public function show(): JsonResponse
    {
        $rw = MasterRw::with('village.subdistrict.district.province', 'ketuaRw:id,name')->first();

        return response()->json(['data' => $rw ? new MasterRwResource($rw) : null]);
    }

    public function update(UpdateMasterRwRequest $request): JsonResponse
    {
        $rw = MasterRw::query()->first();

        if ($rw) {
            $rw->update($request->validated());
        } else {
            $rw = MasterRw::create($request->validated());
        }

        return response()->json(['data' => new MasterRwResource($rw)]);
    }
}
