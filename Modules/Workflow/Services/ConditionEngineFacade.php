<?php

namespace Modules\Workflow\Services;

class ConditionEngineFacade
{
    public function __construct(protected ConditionEvaluationService $evaluationService)
    {
    }

    public function evaluate(mixed $definition, array $context = [], array $subject = []): array
    {
        if (is_array($definition)) {
            return $this->evaluationService->evaluate(
                new \Modules\Workflow\Models\ConditionDefinition($definition),
                $context,
                $subject
            );
        }

        return $this->evaluationService->evaluate($definition, $context, $subject);
    }
}
