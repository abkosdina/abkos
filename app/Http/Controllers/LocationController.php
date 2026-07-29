<?php

namespace App\Http\Controllers;

use App\Http\Resources\CityResource;
use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Shared\Services\LocationService;

class LocationController
{
    public function __construct(protected LocationService $locationService)
    {
    }

    public function getProvinces(Request $request)
    {
        $provinces = $this->locationService->getProvinces($request->query('search'));

        return ProvinceResource::collection($provinces);
    }

    public function getCitiesByProvince(Request $request, $provinceId)
    {
        $cities = $this->locationService->getCitiesByProvince($provinceId, $request->query('search'));

        return CityResource::collection($cities);
    }
}

