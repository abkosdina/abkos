<?php

namespace Modules\Workflow\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workflow\Models\ConditionDefinition;
use Modules\Workflow\Models\ConditionEvaluation;
use Modules\Workflow\Models\ConditionGroup;
use Modules\Workflow\Models\ConditionRule;
use Modules\Workflow\Services\ConditionEvaluationService;
use Tests\TestCase;

class ConditionEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_nested_groups_can_be_evaluated_with_explainable_output(): void
    {
        $user = User::factory()->create();

        $definition = ConditionDefinition::create([
            'name' => 'KYC Review Gate',
            'key' => 'kyc-review-gate',
            'description' => 'Generic gate for review workflows',
            'version' => 1,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $rootGroup = ConditionGroup::create([
            'condition_definition_id' => $definition->id,
            'parent_group_id' => null,
            'logical_operator' => 'AND',
            'sort_order' => 1,
        ]);

        $nestedGroup = ConditionGroup::create([
            'condition_definition_id' => $definition->id,
            'parent_group_id' => $rootGroup->id,
            'logical_operator' => 'OR',
            'sort_order' => 1,
        ]);

        ConditionRule::create([
            'condition_definition_id' => $definition->id,
            'condition_group_id' => $nestedGroup->id,
            'field_path' => 'amount',
            'provider' => 'context',
            'operator' => 'equals',
            'expected_value' => 100,
            'sort_order' => 1,
        ]);

        ConditionRule::create([
            'condition_definition_id' => $definition->id,
            'condition_group_id' => $nestedGroup->id,
            'field_path' => 'status',
            'provider' => 'context',
            'operator' => 'equals',
            'expected_value' => 'approved',
            'sort_order' => 2,
        ]);

        ConditionRule::create([
            'condition_definition_id' => $definition->id,
            'condition_group_id' => $rootGroup->id,
            'field_path' => 'country',
            'provider' => 'context',
            'operator' => 'equals',
            'expected_value' => 'US',
            'sort_order' => 2,
        ]);

        $service = app(ConditionEvaluationService::class);
        $context = [
            'amount' => 100,
            'status' => 'pending',
            'country' => 'US',
        ];

        $result = $service->evaluate($definition, $context, [
            'subject_type' => 'test',
            'subject_id' => 1,
        ]);

        $this->assertTrue($result['passed']);
        $this->assertStringContainsString('AND', $result['explanation']);
        $this->assertSame('passed', $result['status']);
        $this->assertSame(1, ConditionEvaluation::query()->count());
    }

    public function test_invalid_paths_are_rejected_before_evaluation(): void
    {
        $user = User::factory()->create();

        $definition = ConditionDefinition::create([
            'name' => 'Unsafe Path Guard',
            'key' => 'unsafe-path-guard',
            'version' => 1,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $group = ConditionGroup::create([
            'condition_definition_id' => $definition->id,
            'logical_operator' => 'AND',
            'sort_order' => 1,
        ]);

        ConditionRule::create([
            'condition_definition_id' => $definition->id,
            'condition_group_id' => $group->id,
            'field_path' => '../unsafe',
            'provider' => 'context',
            'operator' => 'equals',
            'expected_value' => 'value',
            'sort_order' => 1,
        ]);

        $service = app(ConditionEvaluationService::class);

        $result = $service->evaluate($definition, ['name' => 'safe']);

        $this->assertFalse($result['passed']);
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsString('Invalid field path', $result['explanation']);
    }
}
