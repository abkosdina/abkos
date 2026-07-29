<?php

namespace App\Services\Workflow;

use App\Models\WorkflowAction;

class ActionDefinition
{
    public function __construct(public WorkflowAction $action)
    {
    }

    public static function fromModel(WorkflowAction $action): self
    {
        return new self($action);
    }

    public function getId(): int
    {
        return $this->action->id;
    }

    public function getUuid(): string
    {
        return $this->action->uuid;
    }

    public function getKey(): string
    {
        return $this->action->key ?? $this->action->action_type;
    }

    public function getName(): ?string
    {
        return $this->action->name;
    }

    public function getDescription(): ?string
    {
        return $this->action->description;
    }

    public function getHandlerKey(): string
    {
        return $this->action->handler ?? $this->action->action_type;
    }

    public function getConfiguration(): array
    {
        return $this->action->configuration ?? [];
    }

    public function getPayload(): array
    {
        return $this->action->payload ?? [];
    }

    public function isActive(): bool
    {
        return (bool) $this->action->is_active;
    }

    public function getVersion(): int
    {
        return (int) $this->action->version;
    }

    public function isBlocking(): bool
    {
        return (bool) ($this->action->blocking ?? true);
    }

    public function getPriority(): int
    {
        return (int) ($this->action->priority ?? 100);
    }

    public function getExecutionOrder(): int
    {
        return (int) ($this->action->execution_order ?? 1);
    }

    public function getFailurePolicy(): string
    {
        return $this->action->failure_policy ?? 'stop';
    }

    public function getMaxAttempts(): int
    {
        return (int) ($this->action->max_attempts ?? 3);
    }

    public function getBackoffSeconds(): int
    {
        return (int) ($this->action->backoff_seconds ?? 60);
    }

    public function getMetadata(): array
    {
        return $this->action->metadata ?? [];
    }
}
