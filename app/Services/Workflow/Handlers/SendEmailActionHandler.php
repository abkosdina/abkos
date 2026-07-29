<?php

namespace App\Services\Workflow\Handlers;

use App\Services\Workflow\ActionContext;
use App\Services\Workflow\ActionHandlerInterface;
use App\Services\Workflow\ActionResult;

class SendEmailActionHandler implements ActionHandlerInterface
{
    public function handle(ActionContext $context, array $configuration): ActionResult
    {
        return ActionResult::success([
            'email' => [
                'to' => $configuration['to'] ?? null,
                'subject' => $configuration['subject'] ?? null,
            ],
        ], 'Email queued', ['handler' => 'send_email']);
    }
}
