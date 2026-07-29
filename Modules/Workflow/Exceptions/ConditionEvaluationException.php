<?php

namespace Modules\Workflow\Exceptions;

use Exception;

class ConditionEvaluationException extends Exception
{
    public function __construct(string $message = 'Condition evaluation failed.', array $context = [])
    {
        parent::__construct($message);
        $this->context = $context;
    }
}
