<?php

namespace App\Services\Workflow\Handlers;

use App\Services\Workflow\ActionContext;
use App\Services\Workflow\ActionHandlerInterface;
use App\Services\Workflow\ActionResult;

class SendNotificationActionHandler implements ActionHandlerInterface
{
    public function handle(ActionContext $context, array $configuration): ActionResult
    {
        return ActionResult::success([
            'notification' => [
                'title' => $configuration['title'] ?? 'Notification',
                'body' => $configuration['body'] ?? null,
                'recipients' => $configuration['recipients'] ?? [],
            ],
        ], 'Notification queued', ['handler' => 'send_notification']);
    }
}
