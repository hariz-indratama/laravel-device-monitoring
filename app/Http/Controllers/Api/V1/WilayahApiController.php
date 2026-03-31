<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WilayahService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahApiController extends Controller
{
    public function __construct(private WilayahService $service) {}

    /**
     * GET /api/v1/wilayah/provinces
     */
    public function provinces(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->provinces(),
        ]);
    }

    /**
     * GET /api/v1/wilayah/cities?province_kode=11
     */
    public function cities(Request $request): JsonResponse
    {
        $request->validate([
            'province_kode' => 'required|string|size:2',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->service->cities($request->query('province_kode')),
        ]);
    }

    /**
     * GET /api/v1/wilayah/districts?city_kode=11.01
     */
    public function districts(Request $request): JsonResponse
    {
        $request->validate([
            'city_kode' => 'required|string|size:5',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->service->districts($request->query('city_kode')),
        ]);
    }

    /**
     * GET /api/v1/wilayah/villages?district_kode=11.01.01
     */
    public function villages(Request $request): JsonResponse
    {
        $request->validate([
            'district_kode' => 'required|string|size:8',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->service->villages($request->query('district_kode')),
        ]);
    }
}
