<?php

namespace Modules\Advertisements\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Advertisements\Resources\AdvertisementListResource;
use Modules\Advertisements\Resources\AdvertisementDetailResource;
use Modules\Advertisements\Resources\AdvertisementFilterResource;
use Modules\Advertisements\Services\AdvertisementDiscoveryService;
use Modules\Advertisements\Services\FavoriteService;
use Modules\Shared\Base\BaseController;

class DiscoveryController extends BaseController
{
    public function __construct(
        protected AdvertisementDiscoveryService $discovery,
        protected FavoriteService $favoriteService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->discovery->search($request->all());

        return response()->json([
            'data' => AdvertisementListResource::collection($result['data']),
            'meta' => $result['meta'] ?? [],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $ad = is_numeric($uuid)
            ? $this->discovery->findById((int) $uuid)
            : $this->discovery->findByUuid($uuid);

        $ad->load(['user', 'loanOffer.bank', 'loanOffer.loanPlan', 'province', 'city']);

        return response()->json(new AdvertisementDetailResource($ad));
    }

    public function filters(Request $request): JsonResponse
    {
        $filters = $this->discovery->availableFilters();

        return response()->json(new AdvertisementFilterResource($filters));
    }

    public function similar(string $uuid): JsonResponse
    {
        $list = $this->discovery->similar($uuid, request()->all());

        return response()->json(AdvertisementListResource::collection($list));
    }

    public function recommended(): JsonResponse
    {
        $list = $this->discovery->recommended(request()->all());

        return response()->json(AdvertisementListResource::collection($list));
    }

    public function popular(): JsonResponse
    {
        $list = $this->discovery->popular(request()->all());

        return response()->json(AdvertisementListResource::collection($list));
    }

    public function latest(): JsonResponse
    {
        $list = $this->discovery->latest(request()->all());

        return response()->json(AdvertisementListResource::collection($list));
    }

    public function trending(): JsonResponse
    {
        $list = $this->discovery->trending(request()->all());

        return response()->json(AdvertisementListResource::collection($list));
    }

    public function favorite(string $uuid): JsonResponse
    {
        $this->favoriteService->favorite(auth()->id(), $uuid);

        return response()->json(['success' => true]);
    }

    public function unfavorite(string $uuid): JsonResponse
    {
        $this->favoriteService->unfavorite(auth()->id(), $uuid);

        return response()->json(['success' => true]);
    }

    public function userFavorites(): JsonResponse
    {
        $list = $this->favoriteService->listForUser(auth()->id(), request()->all());

        return response()->json(AdvertisementListResource::collection($list));
    }

    public function recentlyViewed(): JsonResponse
    {
        $list = $this->discovery->recentlyViewed(auth()->id());

        return response()->json(AdvertisementListResource::collection($list));
    }
}
