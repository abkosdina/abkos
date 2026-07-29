<?php

namespace Modules\Workflow\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'ok', 'data' => []]);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['message' => 'created'], 201);
    }

    public function show($workflow): JsonResponse
    {
        return response()->json(['message' => 'show', 'id' => $workflow]);
    }

    public function update(Request $request, $workflow): JsonResponse
    {
        return response()->json(['message' => 'updated', 'id' => $workflow]);
    }

    public function activate($workflow): JsonResponse
    {
        return response()->json(['message' => 'activated', 'id' => $workflow]);
    }

    public function deactivate($workflow): JsonResponse
    {
        return response()->json(['message' => 'deactivated', 'id' => $workflow]);
    }

    public function createVersion(Request $request, $workflow): JsonResponse
    {
        return response()->json(['message' => 'version_created', 'id' => $workflow], 201);
    }

    public function versions($workflow): JsonResponse
    {
        return response()->json(['message' => 'versions', 'id' => $workflow, 'versions' => []]);
    }
}
