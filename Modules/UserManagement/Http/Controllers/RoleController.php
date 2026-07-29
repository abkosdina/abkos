<?php

namespace Modules\UserManagement\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Shared\Base\BaseController;
use Modules\UserManagement\Services\RoleService;

class RoleController extends BaseController
{
    public function __construct(protected RoleService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->all()]);
    }
}
