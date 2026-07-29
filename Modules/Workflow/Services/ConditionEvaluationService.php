<?php

namespace Modules\Workflow\Services;

use Illuminate\Support\Arr;
use Modules\Workflow\Models\ConditionDefinition;
use Modules\Workflow\Models\ConditionEvaluation;
use Modules\Workflow\Models\ConditionGroup;
use Modules\Workflow\Models\ConditionRule;

class ConditionEvaluationService
{
    public function evaluate(ConditionDefinition $definition, array $context = [], array $subject = []): array
    {
        $rootGroups = $definition->groups()->whereNull('parent_group_id')->orderBy('sort_order')->get();

        $results = [];
        $explanationParts = [];
        $overallPassed = true;

        foreach ($rootGroups as $group) {
            $groupResult = $this->evaluateGroup($group, $context, $subject);
            $results[] = $groupResult;
            $explanationParts[] = $groupResult['explanation'];
            $overallPassed = $overallPassed && $groupResult['passed'];
        }

        if ($rootGroups->isEmpty()) {
            $overallPassed = false;
            $explanation = 'No condition groups defined.';
        } else {
            $explanation = 'Condition groups evaluated: ' . implode(' | ', $explanationParts);
        }

        $evaluation = ConditionEvaluation::create([
            'condition_definition_id' => $definition->id,
            'subject_type' => $subject['subject_type'] ?? null,
            'subject_id' => $subject['subject_id'] ?? null,
            'passed' => $overallPassed,
            'status' => $overallPassed ? 'passed' : 'failed',
            'explanation' => $explanation,
            'metadata' => ['context' => $context, 'subject' => $subject],
            'result_payload' => [
                'passed' => $overallPassed,
                'groups' => $results,
            ],
        ]);

        return [
            'passed' => $overallPassed,
            'status' => $evaluation->status,
            'explanation' => $explanation,
            'evaluation_id' => $evaluation->id,
            'payload' => $evaluation->result_payload,
        ];
    }

    protected function evaluateGroup(ConditionGroup $group, array $context = [], array $subject = []): array
    {
        $rules = $group->rules()->get();
        $childGroups = $group->childGroups()->get();

        $ruleResults = [];
        $explanationParts = [];

        foreach ($rules as $rule) {
            $ruleResult = $this->evaluateRule($rule, $context, $subject);
            $ruleResults[] = $ruleResult;
            $explanationParts[] = $ruleResult['explanation'];
        }

        foreach ($childGroups as $childGroup) {
            $childResult = $this->evaluateGroup($childGroup, $context, $subject);
            $ruleResults[] = $childResult;
            $explanationParts[] = $childResult['explanation'];
        }

        $passed = $this->combineResults($group->logical_operator, $ruleResults);
        $explanation = sprintf('%s group evaluated with %s rule(s): %s', $group->logical_operator, count($ruleResults), implode(' | ', $explanationParts));

        return [
            'passed' => $passed,
            'explanation' => $explanation,
            'logical_operator' => $group->logical_operator,
            'rules' => $ruleResults,
        ];
    }

    protected function evaluateRule(ConditionRule $rule, array $context = [], array $subject = []): array
    {
        $fieldPath = $rule->field_path;
        if ($this->isUnsafePath($fieldPath)) {
            return [
                'passed' => false,
                'explanation' => 'Invalid field path: ' . $fieldPath,
                'rule' => $rule->toArray(),
            ];
        }

        $value = $this->resolveValue($fieldPath, $context, $subject);
        $expectedValue = $rule->expected_value;
        $operator = $rule->operator;

        $passed = $this->applyOperator($operator, $value, $expectedValue);

        return [
            'passed' => $passed,
            'explanation' => sprintf('%s %s %s => %s', $fieldPath, $operator, json_encode($expectedValue), $passed ? 'true' : 'false'),
            'rule' => $rule->toArray(),
            'value' => $value,
        ];
    }

    protected function resolveValue(string $fieldPath, array $context = [], array $subject = []): mixed
    {
        if ($fieldPath === 'subject') {
            return $subject;
        }

        if ($fieldPath === 'context') {
            return $context;
        }

        $value = Arr::get($context, $fieldPath);
        if ($value !== null || Arr::has($context, $fieldPath)) {
            return $value;
        }

        return null;
    }

    protected function applyOperator(string $operator, mixed $value, mixed $expectedValue): bool
    {
        return match ($operator) {
            'equals' => $value == $expectedValue,
            'not_equals' => $value != $expectedValue,
            'greater_than' => $value > $expectedValue,
            'less_than' => $value < $expectedValue,
            'in' => is_array($expectedValue) && in_array($value, $expectedValue, true),
            'contains' => is_string($value) && is_string($expectedValue) && str_contains($value, $expectedValue),
            default => false,
        };
    }

    protected function combineResults(string $logicalOperator, array $results): bool
    {
        if ($results === []) {
            return false;
        }

        $passedResults = array_values(array_filter($results, static fn ($result) => $result['passed'] ?? false));

        return match (strtoupper($logicalOperator)) {
            'AND' => count($passedResults) === count($results),
            'OR' => count($passedResults) > 0,
            'NOT' => count($passedResults) === 0,
            default => false,
        };
    }

    protected function isUnsafePath(string $fieldPath): bool
    {
        return str_contains($fieldPath, '..') || str_contains($fieldPath, '\\') || str_contains($fieldPath, '://');
    }
}
