<?php

namespace Modules\Advertisements\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Modules\Advertisements\Actions\SubmitAdvertisementAction;
use Modules\Advertisements\Actions\ApproveAdvertisementAction;
use Modules\Advertisements\Actions\RejectAdvertisementAction;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Policies\AdvertisementPolicy;

class AdvertisementWorkflowController extends Controller
{
    public function submit(Request $request, $uuid, SubmitAdvertisementAction $action)
    {
        $ad = Advertisement::where('uuid', $uuid)->firstOrFail();
        $policy = new AdvertisementPolicy();

        if (! $policy->submit($request->user(), $ad)) {
            throw new AuthorizationException();
        }

        $action->execute($request->user(), $ad, [
            'reason' => $request->input('reason'),
            'comment' => $request->input('comment'),
            'data' => $request->input('data', []),
            'ip' => $request->ip(),
            'device' => $request->header('User-Agent'),
        ]);

        return response()->json(['status' => 'submitted']);
    }

    public function approve(Request $request, $uuid, ApproveAdvertisementAction $action)
    {
        $ad = Advertisement::where('uuid', $uuid)->firstOrFail();
        $policy = new AdvertisementPolicy();

        if (! $policy->approve($request->user(), $ad)) {
            throw new AuthorizationException();
        }

        $action->execute($request->user(), $ad, [
            'reason' => $request->input('reason'),
            'comment' => $request->input('comment'),
            'ip' => $request->ip(),
            'device' => $request->header('User-Agent'),
        ]);

        return response()->json(['status' => 'approved']);
    }

    public function reject(Request $request, $uuid, RejectAdvertisementAction $action)
    {
        $ad = Advertisement::where('uuid', $uuid)->firstOrFail();
        $policy = new AdvertisementPolicy();

        if (! $policy->reject($request->user(), $ad)) {
            throw new AuthorizationException();
        }

        $action->execute($request->user(), $ad, [
            'reason' => $request->input('reason'),
            'comment' => $request->input('comment'),
            'ip' => $request->ip(),
            'device' => $request->header('User-Agent'),
        ]);

        return response()->json(['status' => 'rejected']);
    }
}
