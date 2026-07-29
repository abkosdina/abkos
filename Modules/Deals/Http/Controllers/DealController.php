<?php

namespace Modules\Deals\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Deals\Models\Deal;
use Modules\Deals\Resources\DealResource;
use Modules\Shared\Base\BaseController;

class DealController extends BaseController
{
    public function index(): JsonResponse
    {
        $deals = Deal::query()->with(['negotiation', 'advertisement', 'buyer', 'seller'])->get();

        return response()->json([
            'success' => true,
            'data' => DealResource::collection($deals),
            'message' => 'Deals retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $deal = Deal::query()->where('uuid', $uuid)->with(['negotiation', 'advertisement', 'buyer', 'seller'])->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new DealResource($deal),
            'message' => 'Deal retrieved successfully.',
        ]);
    }

    public function myDeals(): JsonResponse
    {
        $userId = auth()->id();

        $deals = Deal::query()
            ->where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)->orWhere('seller_id', $userId);
            })
            ->with(['negotiation', 'advertisement', 'buyer', 'seller'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => DealResource::collection($deals),
        ]);
    }
}
