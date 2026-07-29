<?php

namespace Modules\Deals\Services;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Services\Workflow\WorkflowEngine;
use Modules\Deals\Database\Seeders\DealWorkflowDefinitionSeeder;
use Modules\Deals\Models\Deal;

class DealWorkflowService
{
    public function __construct(protected WorkflowEngine $workflowEngine)
    {
    }

    public function createDealWorkflow(Deal $deal): WorkflowInstance
    {
        $definition = $this->getOrCreateDealWorkflowDefinition();

        return $this->workflowEngine->start(
            $definition,
            'Deal',
            $deal->id,
            ['negotiation_id' => $deal->negotiation_id]
        );
    }

    protected function getOrCreateDealWorkflowDefinition(): WorkflowDefinition
    {
        $definition = $this->workflowEngine->getDefinition('deal.lifecycle');

        if ($definition) {
            return $definition;
        }

        app(DealWorkflowDefinitionSeeder::class)->run();

        $definition = $this->workflowEngine->getDefinition('deal.lifecycle');

        if (! $definition) {
            throw new \Exception('Deal workflow definition could not be created.');
        }

        return $definition;
    }
}
