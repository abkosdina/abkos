<?php

namespace Modules\Workflow\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Workflow\Models\ApprovalDefinition;
use Modules\Workflow\Enums\ApprovalMode;

class ApprovalDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        if (ApprovalDefinition::query()->exists()) {
            return;
        }

        $workflowDefinition = \Modules\Workflow\Models\WorkflowDefinition::query()->where('key', 'generic-review-demo')->first();

        if (! $workflowDefinition) {
            return;
        }

        ApprovalDefinition::create([
            'workflow_definition_id' => $workflowDefinition->id,
            'name' => 'Development Demo Approval Definition',
            'key' => 'development-demo-approval',
            'description' => 'Development/demo approval definition for generic workflow testing.',
            'approval_mode' => ApprovalMode::any->value,
            'required_approval_count' => 1,
            'is_active' => true,
            'configuration' => ['demo' => true],
        ]);
    }
}
