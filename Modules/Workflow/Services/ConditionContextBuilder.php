<?php

namespace Modules\Workflow\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;
use Modules\Workflow\Models\ConditionDefinition;

class ConditionContextBuilder
{
    public function buildForWorkflow(mixed $workflowInstance, mixed $transition = null, ?User $user = null, array $payload = []): array
    {
        $context = [];
        $context['user'] = $this->sanitizeUser($user, $workflowInstance, $payload);
        $context['actor'] = $context['user'];
        $context['workflow'] = $this->sanitizeWorkflow($workflowInstance);
        $context['workflow_instance'] = $context['workflow'];
        $context['workflow_state'] = $this->sanitizeState($workflowInstance->currentState ?? null);
        $context['business_entity'] = $this->sanitizeEntity($workflowInstance);
        $context['metadata'] = Arr::get($payload, 'metadata', []);
        $context['transition'] = $transition ? ['id' => $transition->id, 'key' => $transition->key ?? null] : null;
        $context['payload'] = Arr::except($payload, ['metadata']);

        return $this->filterSensitive($context);
    }

    public function buildForApproval(ApprovalInstance $approvalInstance, ApprovalInstanceStep $approvalInstanceStep, ?User $user = null, array $payload = []): array
    {
        $workflowInstance = $approvalInstance->workflowInstance;
        $context = [];
        $context['user'] = $this->sanitizeUser($user, $workflowInstance, $payload);
        $context['actor'] = $context['user'];
        $context['approval'] = [
            'id' => $approvalInstance->id,
            'status' => $approvalInstance->status,
            'required_approval_count' => $approvalInstance->required_approval_count,
        ];
        $context['approval_instance'] = $context['approval'];
        $context['approval_step'] = [
            'id' => $approvalInstanceStep->id,
            'status' => $approvalInstanceStep->status,
            'required_approval_count' => $approvalInstanceStep->required_approval_count,
        ];
        $context['workflow'] = $this->sanitizeWorkflow($workflowInstance);
        $context['workflow_instance'] = $context['workflow'];
        $context['workflow_state'] = $this->sanitizeState($workflowInstance?->currentState);
        $context['business_entity'] = $this->sanitizeEntity($workflowInstance);
        $context['metadata'] = Arr::get($payload, 'metadata', []);
        $context['payload'] = Arr::except($payload, ['metadata']);

        return $this->filterSensitive($context);
    }

    protected function sanitizeUser(?User $user, mixed $workflowInstance = null, array $payload = []): array
    {
        $userData = [];

        if ($user) {
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        }

        $workflowMetadata = [];
        if ($workflowInstance) {
            $workflowMetadata = Arr::get($workflowInstance->metadata ?? [], 'user', []);
        }

        $payloadMetadata = Arr::get($payload, 'metadata.user', []);

        return array_merge($userData, $workflowMetadata, $payloadMetadata);
    }

    protected function sanitizeWorkflow(mixed $workflowInstance): array
    {
        if (! $workflowInstance) {
            return [];
        }

        return [
            'id' => $workflowInstance->id,
            'status' => $workflowInstance->status,
            'version' => $workflowInstance->version,
            'definition_id' => $workflowInstance->workflow_definition_id,
        ];
    }

    protected function sanitizeState(mixed $state): array
    {
        if (! $state) {
            return [];
        }

        return [
            'id' => $state->id,
            'key' => $state->key,
            'name' => $state->name,
        ];
    }

    protected function sanitizeEntity(mixed $workflowInstance): array
    {
        if (! $workflowInstance) {
            return [];
        }

        return [
            'type' => $workflowInstance->entity_type,
            'id' => $workflowInstance->entity_id,
        ];
    }

    protected function filterSensitive(array $context): array
    {
        foreach (array_keys($context) as $key) {
            if (is_array($context[$key])) {
                $context[$key] = Arr::except($context[$key], ['password', 'otp', 'token', 'payment_credentials', 'secret', 'private_key']);
            }
        }

        return $context;
    }
}
