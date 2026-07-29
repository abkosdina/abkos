<?php

namespace Modules\Workflow\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowApprovalController extends Controller
{
    public function approve($instance): JsonResponse
    {
        return response()->json(['message' => 'approved', 'id' => $instance]);
    }

    public function reject($instance): JsonResponse
    {
        return response()->json(['message' => 'rejected', 'id' => $instance]);
    }

    public function return($instance): JsonResponse
    {
        return response()->json(['message' => 'returned', 'id' => $instance]);
    }

    public function cancel($instance): JsonResponse
    {
        return response()->json(['message' => 'cancelled', 'id' => $instance]);
    }
}
