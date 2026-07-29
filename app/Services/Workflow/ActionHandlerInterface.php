<?php

namespace App\Services\Workflow;

interface ActionHandlerInterface
{
    public function handle(ActionContext $context, array $configuration): ActionResult;
}
