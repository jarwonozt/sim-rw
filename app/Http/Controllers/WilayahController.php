<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Province;
use App\Models\Subdistrict;
use App\Models\Village;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint JSON ringan untuk dropdown wilayah berjenjang
 * (Provinsi -> Kabupaten/Kota -> Kecamatan -> Kelurahan) di form RW.
 */
class WilayahController extends Controller
{
    public function provinces(): JsonResponse
    {
        return response()->json(
            Province::query()->orderBy('name')->get(['id', 'name'])
        );
    }

    public function districts(Request $request): JsonResponse
    {
        $request->validate(['province_id' => ['required', 'integer']]);

        return response()->json(
            District::query()
                ->where('province_id', $request->integer('province_id'))
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function subdistricts(Request $request): JsonResponse
    {
        $request->validate(['district_id' => ['required', 'integer']]);

        return response()->json(
            Subdistrict::query()
                ->where('district_id', $request->integer('district_id'))
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function villages(Request $request): JsonResponse
    {
        $request->validate(['subdistrict_id' => ['required', 'integer']]);

        return response()->json(
            Village::query()
                ->where('subdistrict_id', $request->integer('subdistrict_id'))
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }
}
