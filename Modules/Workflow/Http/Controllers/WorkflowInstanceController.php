<?php

namespace Modules\Workflow\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowInstanceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'instances', 'data' => []]);
    }

    public function show($instance): JsonResponse
    {
        return response()->json(['message' => 'instance', 'id' => $instance]);
    }

    public function start(Request $request, $workflow): JsonResponse
    {
        return response()->json(['message' => 'started', 'workflow' => $workflow], 201);
    }
}
