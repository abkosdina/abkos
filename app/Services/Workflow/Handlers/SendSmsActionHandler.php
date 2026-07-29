<?php

namespace App\Services\Workflow\Handlers;

use App\Services\Workflow\ActionContext;
use App\Services\Workflow\ActionHandlerInterface;
use App\Services\Workflow\ActionResult;

class SendSmsActionHandler implements ActionHandlerInterface
{
    public function handle(ActionContext $context, array $configuration): ActionResult
    {
        return ActionResult::success([
            'sms' => [
                'recipient' => $configuration['recipient'] ?? null,
                'message' => $configuration['message'] ?? null,
            ],
        ], 'SMS queued', ['handler' => 'send_sms']);
    }
}
