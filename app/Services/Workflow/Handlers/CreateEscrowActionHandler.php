<?php

namespace App\Services\Workflow\Handlers;

use App\Services\Workflow\ActionContext;
use App\Services\Workflow\ActionHandlerInterface;
use App\Services\Workflow\ActionResult;

class CreateEscrowActionHandler implements ActionHandlerInterface
{
    public function handle(ActionContext $context, array $configuration): ActionResult
    {
        return ActionResult::success([ 'escrow' => $configuration ], 'Escrow creation requested', ['handler' => 'create_escrow']);
    }
}
