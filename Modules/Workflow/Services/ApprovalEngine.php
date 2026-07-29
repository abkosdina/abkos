<?php

namespace Modules\Workflow\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Workflow\Enums\ApprovalDecision as ApprovalDecisionEnum;
use Modules\Workflow\Enums\ApprovalInstanceStepStatus;
use Modules\Workflow\Enums\ApprovalMode;
use Modules\Workflow\Enums\ApprovalStatus;
use Modules\Workflow\Events\ApprovalApproved;
use Modules\Workflow\Events\ApprovalCompleted;
use Modules\Workflow\Events\ApprovalRejected;
use Modules\Workflow\Events\ApprovalReturnedForCorrection;
use Modules\Workflow\Events\ApprovalStarted;
use Modules\Workflow\Events\ApprovalStepCompleted;
use Modules\Workflow\Events\ApprovalStepStarted;
use Modules\Workflow\Events\ApprovalSubmitted;
use Modules\Workflow\Exceptions\ApprovalNotActiveException;
use Modules\Workflow\Exceptions\ApprovalStepNotActiveException;
use Modules\Workflow\Exceptions\ConditionEvaluationException;
use Modules\Workflow\Exceptions\DuplicateApprovalDecisionException;
use Modules\Workflow\Exceptions\UnauthorizedApprovalException;
use Modules\Workflow\Interfaces\ApprovalEngineInterface;
use Modules\Workflow\Models\ApprovalDefinition;
use Modules\Workflow\Services\ApprovalAuthorizationService;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;
use Modules\Workflow\Models\WorkflowInstance;

class ApprovalEngine implements ApprovalEngineInterface
{
    public function __construct(
        protected ApprovalAuthorizationService $authorizationService,
        protected ConditionEvaluationService $conditionEvaluationService,
        protected ConditionContextBuilder $conditionContextBuilder,
    ) {
    }

    public function start(WorkflowInstance $workflowInstance, ApprovalDefinition $approvalDefinition): ApprovalInstance
    {
        if (! $approvalDefinition->is_active) {
            throw new ApprovalNotActiveException('Approval definition is not active.');
        }

        return DB::transaction(function () use ($workflowInstance, $approvalDefinition): ApprovalInstance {
            $existing = ApprovalInstance::query()
                ->where('workflow_instance_id', $workflowInstance->id)
                ->where('approval_definition_id', $approvalDefinition->id)
                ->whereIn('status', [ApprovalStatus::pending->value, ApprovalStatus::in_progress->value])
                ->first();

            if ($existing) {
                return $existing;
            }

            $instance = ApprovalInstance::create([
                'workflow_instance_id' => $workflowInstance->id,
                'approval_definition_id' => $approvalDefinition->id,
                'status' => ApprovalStatus::in_progress->value,
                'required_approval_count' => max(1, (int) $approvalDefinition->required_approval_count),
                'received_approval_count' => 0,
                'version' => 1,
                'started_at' => now(),
                'metadata' => ['started_via' => 'engine'],
            ]);

            $steps = $approvalDefinition->approvalSteps()->orderBy('sort_order')->get();
            $initialSteps = $steps->filter(fn (ApprovalStep $step) => $step->sort_order === 1);

            foreach ($initialSteps as $step) {
                ApprovalInstanceStep::create([
                    'approval_instance_id' => $instance->id,
                    'approval_step_id' => $step->id,
                    'status' => ApprovalInstanceStepStatus::active->value,
                    'required_approval_count' => max(1, (int) $step->required_approval_count),
                    'received_approval_count' => 0,
                    'version' => 1,
                    'started_at' => now(),
                    'metadata' => ['created_via' => 'engine'],
                ]);
            }

            event(new ApprovalStarted($instance));
            if ($instance->approvalInstanceSteps()->exists()) {
                $firstStep = $instance->approvalInstanceSteps()->first();
                event(new ApprovalStepStarted($instance, $firstStep));
            }

            return $instance;
        });
    }

    public function approve(ApprovalInstance $approvalInstance, User $user, array $payload = []): ApprovalDecision
    {
        return DB::transaction(function () use ($approvalInstance, $user, $payload): ApprovalDecision {
            $approvalInstance->refresh();
            $approvalInstance->lockForUpdate();

            $idempotencyKey = Arr::get($payload, 'idempotency_key') ?: (string) Str::uuid();
            $existingByIdempotency = ApprovalDecision::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingByIdempotency) {
                return $existingByIdempotency;
            }

            if ($approvalInstance->status === ApprovalStatus::approved || $approvalInstance->status === ApprovalStatus::rejected || $approvalInstance->status === ApprovalStatus::returned_for_correction) {
                throw new ApprovalNotActiveException('Approval instance is no longer active.');
            }

            $stepId = Arr::get($payload, 'approval_instance_step_id') ?? $approvalInstance->approvalInstanceSteps()->where('status', ApprovalInstanceStepStatus::active->value)->first()?->id;
            $step = $approvalInstance->approvalInstanceSteps()->whereKey($stepId)->firstOrFail();
            $step->refresh();
            $step->lockForUpdate();

            if ($step->status !== ApprovalInstanceStepStatus::active) {
                throw new ApprovalStepNotActiveException('Approval step is not active.');
            }

            $duplicateByApprover = ApprovalDecision::query()
                ->where('approval_instance_id', $approvalInstance->id)
                ->where('approval_instance_step_id', $step->id)
                ->where('approver_id', $user->id)
                ->exists();

            if ($duplicateByApprover) {
                throw new DuplicateApprovalDecisionException('A decision already exists for this approver on this step.');
            }

            $conditionContext = $this->conditionContextBuilder->buildForApproval($approvalInstance, $step, $user, Arr::get($payload, 'metadata', []));
            $conditionDefinition = $this->resolveConditionDefinition($approvalInstance, $step, $payload);
            if ($conditionDefinition) {
                $result = $this->conditionEvaluationService->evaluate($conditionDefinition, $conditionContext, [
                    'subject_type' => ApprovalInstance::class,
                    'subject_id' => $approvalInstance->id,
                ]);
                if (! $result['passed']) {
                    throw new ConditionEvaluationException($result['explanation'] ?? 'Approval conditions were not satisfied.', [
                        'approval_instance_id' => $approvalInstance->id,
                        'approval_step_id' => $step->id,
                        'condition_result' => $result,
                    ]);
                }
            }

            $authorization = $this->authorizationService->canApprove($user, $approvalInstance, $step);
            if (! $authorization->authorized) {
                throw new UnauthorizedApprovalException($authorization->message);
            }
            if ($conditionDefinition) {
                $result = $this->conditionEvaluationService->evaluate($conditionDefinition, $conditionContext, [
                    'subject_type' => ApprovalInstance::class,
                    'subject_id' => $approvalInstance->id,
                ]);
                if (! $result['passed']) {
                    throw new ConditionEvaluationException($result['explanation'] ?? 'Approval conditions were not satisfied.', [
                        'approval_instance_id' => $approvalInstance->id,
                        'approval_step_id' => $step->id,
                        'condition_result' => $result,
                    ]);
                }
            }

            $decision = ApprovalDecision::create([
                'approval_instance_id' => $approvalInstance->id,
                'approval_instance_step_id' => $step->id,
                'approver_id' => $user->id,
                'approver_role' => Arr::get($payload, 'approver_role') ?: 'reviewer',
                'decision' => ApprovalDecisionEnum::approved->value,
                'comment' => Arr::get($payload, 'comment'),
                'reason' => Arr::get($payload, 'reason'),
                'idempotency_key' => $idempotencyKey,
                'metadata' => Arr::get($payload, 'metadata', []),
                'decided_at' => now(),
            ]);

            $step->received_approval_count = (int) $step->received_approval_count + 1;
            if ($step->received_approval_count >= (int) $step->required_approval_count) {
                $step->status = ApprovalInstanceStepStatus::approved->value;
                $step->completed_at = now();
            }
            $step->save();

            $approvalInstance->received_approval_count = (int) $approvalInstance->received_approval_count + 1;
            $approvalInstance->save();

            $approvalDefinition = $approvalInstance->approvalDefinition()->firstOrFail();
            $mode = $approvalDefinition->approval_mode instanceof ApprovalMode
                ? $approvalDefinition->approval_mode->value
                : $approvalDefinition->approval_mode;

            $shouldComplete = $this->shouldCompleteApproval($approvalDefinition, $approvalInstance, $step);

            if ($shouldComplete) {
                $approvalInstance->status = ApprovalStatus::approved->value;
                $approvalInstance->completed_at = now();
                $approvalInstance->save();
            } elseif ($mode === ApprovalMode::sequential->value && $step->status === ApprovalInstanceStepStatus::approved) {
                $nextStep = $this->activateNextStep($approvalInstance, $step);
                if ($nextStep) {
                    event(new ApprovalStepStarted($approvalInstance, $nextStep));
                }
            }

            event(new ApprovalSubmitted($approvalInstance, $step, $decision, $user));
            event(new ApprovalApproved($approvalInstance, $step, $decision, $user));
            event(new ApprovalStepCompleted($approvalInstance, $step, $decision, $user));
            if ($approvalInstance->status === ApprovalStatus::approved) {
                event(new ApprovalCompleted($approvalInstance, $step, $decision, $user));
            }

            return $decision;
        });
    }

    public function reject(ApprovalInstance $approvalInstance, User $user, string $reason, ?string $comment = null, array $payload = []): ApprovalDecision
    {
        return DB::transaction(function () use ($approvalInstance, $user, $reason, $comment, $payload): ApprovalDecision {
            $approvalInstance->refresh();
            $approvalInstance->lockForUpdate();

            if ($approvalInstance->status === ApprovalStatus::approved || $approvalInstance->status === ApprovalStatus::rejected || $approvalInstance->status === ApprovalStatus::returned_for_correction) {
                throw new ApprovalNotActiveException('Approval instance is no longer active.');
            }

            $stepId = Arr::get($payload, 'approval_instance_step_id') ?? $approvalInstance->approvalInstanceSteps()->where('status', ApprovalInstanceStepStatus::active->value)->first()?->id;
            $step = $approvalInstance->approvalInstanceSteps()->whereKey($stepId)->firstOrFail();
            $step->refresh();
            $step->lockForUpdate();

            $idempotencyKey = Arr::get($payload, 'idempotency_key') ?: (string) Str::uuid();
            $existing = ApprovalDecision::query()
                ->where('approval_instance_id', $approvalInstance->id)
                ->where('approval_instance_step_id', $step->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            $conditionContext = $this->conditionContextBuilder->buildForApproval($approvalInstance, $step, $user, Arr::get($payload, 'metadata', []));
            $conditionDefinition = $this->resolveConditionDefinition($approvalInstance, $step, $payload);
            if ($conditionDefinition) {
                $result = $this->conditionEvaluationService->evaluate($conditionDefinition, $conditionContext, [
                    'subject_type' => ApprovalInstance::class,
                    'subject_id' => $approvalInstance->id,
                ]);
                if (! $result['passed']) {
                    throw new ConditionEvaluationException($result['explanation'] ?? 'Approval conditions were not satisfied.', [
                        'approval_instance_id' => $approvalInstance->id,
                        'approval_step_id' => $step->id,
                        'condition_result' => $result,
                    ]);
                }
            }

            $authorization = $this->authorizationService->canReject($user, $approvalInstance, $step);
            if (! $authorization->authorized) {
                throw new UnauthorizedApprovalException($authorization->message);
            }
            if ($conditionDefinition) {
                $result = $this->conditionEvaluationService->evaluate($conditionDefinition, $conditionContext, [
                    'subject_type' => ApprovalInstance::class,
                    'subject_id' => $approvalInstance->id,
                ]);
                if (! $result['passed']) {
                    throw new ConditionEvaluationException($result['explanation'] ?? 'Approval conditions were not satisfied.', [
                        'approval_instance_id' => $approvalInstance->id,
                        'approval_step_id' => $step->id,
                        'condition_result' => $result,
                    ]);
                }
            }

            $decision = ApprovalDecision::create([
                'approval_instance_id' => $approvalInstance->id,
                'approval_instance_step_id' => $step->id,
                'approver_id' => $user->id,
                'approver_role' => Arr::get($payload, 'approver_role') ?: 'reviewer',
                'decision' => ApprovalDecisionEnum::rejected->value,
                'comment' => $comment,
                'reason' => $reason,
                'idempotency_key' => $idempotencyKey,
                'metadata' => Arr::get($payload, 'metadata', []),
                'decided_at' => now(),
            ]);

            $step->status = ApprovalInstanceStepStatus::rejected->value;
            $step->rejected_at = now();
            $step->save();

            $approvalInstance->status = ApprovalStatus::rejected->value;
            $approvalInstance->rejected_at = now();
            $approvalInstance->save();

            event(new ApprovalRejected($approvalInstance, $step, $decision, $user));

            return $decision;
        });
    }

    public function returnForCorrection(ApprovalInstance $approvalInstance, User $user, string $reason, ?string $comment = null, array $payload = []): ApprovalDecision
    {
        return DB::transaction(function () use ($approvalInstance, $user, $reason, $comment, $payload): ApprovalDecision {
            $approvalInstance->refresh();
            $approvalInstance->lockForUpdate();

            if ($approvalInstance->status === ApprovalStatus::approved || $approvalInstance->status === ApprovalStatus::rejected || $approvalInstance->status === ApprovalStatus::returned_for_correction) {
                throw new ApprovalNotActiveException('Approval instance is no longer active.');
            }

            $stepId = Arr::get($payload, 'approval_instance_step_id') ?? $approvalInstance->approvalInstanceSteps()->where('status', ApprovalInstanceStepStatus::active->value)->first()?->id;
            $step = $approvalInstance->approvalInstanceSteps()->whereKey($stepId)->firstOrFail();
            $step->refresh();
            $step->lockForUpdate();

            $idempotencyKey = Arr::get($payload, 'idempotency_key') ?: (string) Str::uuid();
            $existing = ApprovalDecision::query()
                ->where('approval_instance_id', $approvalInstance->id)
                ->where('approval_instance_step_id', $step->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            $conditionContext = $this->conditionContextBuilder->buildForApproval($approvalInstance, $step, $user, Arr::get($payload, 'metadata', []));
            $conditionDefinition = $this->resolveConditionDefinition($approvalInstance, $step, $payload);
            if ($conditionDefinition) {
                $result = $this->conditionEvaluationService->evaluate($conditionDefinition, $conditionContext, [
                    'subject_type' => ApprovalInstance::class,
                    'subject_id' => $approvalInstance->id,
                ]);
                if (! $result['passed']) {
                    throw new ConditionEvaluationException($result['explanation'] ?? 'Approval conditions were not satisfied.', [
                        'approval_instance_id' => $approvalInstance->id,
                        'approval_step_id' => $step->id,
                        'condition_result' => $result,
                    ]);
                }
            }

            $authorization = $this->authorizationService->canReturnForCorrection($user, $approvalInstance, $step);
            if (! $authorization->authorized) {
                throw new UnauthorizedApprovalException($authorization->message);
            }
            if ($conditionDefinition) {
                $result = $this->conditionEvaluationService->evaluate($conditionDefinition, $conditionContext, [
                    'subject_type' => ApprovalInstance::class,
                    'subject_id' => $approvalInstance->id,
                ]);
                if (! $result['passed']) {
                    throw new ConditionEvaluationException($result['explanation'] ?? 'Approval conditions were not satisfied.', [
                        'approval_instance_id' => $approvalInstance->id,
                        'approval_step_id' => $step->id,
                        'condition_result' => $result,
                    ]);
                }
            }

            $decision = ApprovalDecision::create([
                'approval_instance_id' => $approvalInstance->id,
                'approval_instance_step_id' => $step->id,
                'approver_id' => $user->id,
                'approver_role' => Arr::get($payload, 'approver_role') ?: 'reviewer',
                'decision' => ApprovalDecisionEnum::returned_for_correction->value,
                'comment' => $comment,
                'reason' => $reason,
                'idempotency_key' => $idempotencyKey,
                'metadata' => Arr::get($payload, 'metadata', []),
                'decided_at' => now(),
            ]);

            $step->status = ApprovalInstanceStepStatus::returned_for_correction->value;
            $step->returned_at = now();
            $step->save();

            $approvalInstance->status = ApprovalStatus::returned_for_correction->value;
            $approvalInstance->returned_at = now();
            $approvalInstance->save();

            event(new ApprovalReturnedForCorrection($approvalInstance, $step, $decision, $user));

            return $decision;
        });
    }

    protected function resolveConditionDefinition(ApprovalInstance $approvalInstance, ApprovalInstanceStep $step, array $payload = []): ?\Modules\Workflow\Models\ConditionDefinition
    {
        $approvalDefinition = $approvalInstance->approvalDefinition()->first();
        $approvalStep = $step->approvalStep()->first();

        $definitionId = Arr::get($payload, 'condition_definition_id');
        if ($definitionId) {
            return \Modules\Workflow\Models\ConditionDefinition::query()->find($definitionId);
        }

        $approvalConfiguration = $approvalDefinition?->configuration ?? [];
        $stepConfiguration = $approvalStep?->configuration ?? [];
        $conditions = Arr::get($approvalConfiguration, 'pre_conditions', []);
        if ($conditions === []) {
            $conditions = Arr::get($stepConfiguration, 'pre_conditions', []);
        }

        if ($conditions === []) {
            return null;
        }

        $firstCondition = $conditions[0] ?? null;
        if (! isset($firstCondition['condition_definition_id'])) {
            return null;
        }

        return \Modules\Workflow\Models\ConditionDefinition::query()->find($firstCondition['condition_definition_id']);
    }

    public function getStatus(ApprovalInstance $approvalInstance): ApprovalStatus
    {
        return $approvalInstance->status instanceof ApprovalStatus ? $approvalInstance->status : ApprovalStatus::from($approvalInstance->status);
    }

    public function getPendingApprovals(): Collection
    {
        return ApprovalInstance::query()->where('status', ApprovalStatus::in_progress->value)->get();
    }

    public function isApproved(ApprovalInstance $approvalInstance): bool
    {
        return $this->getStatus($approvalInstance) === ApprovalStatus::approved;
    }

    public function isRejected(ApprovalInstance $approvalInstance): bool
    {
        return $this->getStatus($approvalInstance) === ApprovalStatus::rejected;
    }

    public function isCompleted(ApprovalInstance $approvalInstance): bool
    {
        return $this->isApproved($approvalInstance) || $this->isRejected($approvalInstance) || $this->getStatus($approvalInstance) === ApprovalStatus::returned_for_correction;
    }

    protected function shouldCompleteApproval(ApprovalDefinition $approvalDefinition, ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): bool
    {
        $mode = $approvalDefinition->approval_mode?->value ?? $approvalDefinition->approval_mode;

        if ($mode === ApprovalMode::sequential->value) {
            $currentSortOrder = optional($step->approvalStep()->first())->sort_order ?? 0;
            $nextStep = $approvalDefinition->approvalSteps()->where('sort_order', '>', $currentSortOrder)->orderBy('sort_order')->first();
            return $nextStep === null;
        }

        if ($mode === ApprovalMode::any->value) {
            return true;
        }

        if ($mode === ApprovalMode::all->value) {
            $steps = $approvalInstance->approvalInstanceSteps()->get();
            return $steps->every(fn (ApprovalInstanceStep $instanceStep) => $instanceStep->status === ApprovalInstanceStepStatus::approved);
        }

        if ($mode === ApprovalMode::quorum->value) {
            return $approvalInstance->received_approval_count >= max(1, (int) $approvalDefinition->required_approval_count);
        }

        return $approvalInstance->received_approval_count >= max(1, (int) $approvalDefinition->required_approval_count);
    }

    protected function activateNextStep(ApprovalInstance $approvalInstance, ApprovalInstanceStep $completedStep): ?ApprovalInstanceStep
    {
        $approvalDefinition = $approvalInstance->approvalDefinition()->firstOrFail();
        $currentSortOrder = optional($completedStep->approvalStep()->first())->sort_order ?? 0;
        $nextApprovalStep = $approvalDefinition->approvalSteps()
            ->where('sort_order', '>', $currentSortOrder)
            ->orderBy('sort_order')
            ->first();

        if (! $nextApprovalStep) {
            return null;
        }

        return ApprovalInstanceStep::create([
            'approval_instance_id' => $approvalInstance->id,
            'approval_step_id' => $nextApprovalStep->id,
            'status' => ApprovalInstanceStepStatus::active->value,
            'required_approval_count' => max(1, (int) $nextApprovalStep->required_approval_count),
            'received_approval_count' => 0,
            'version' => 1,
            'started_at' => now(),
            'metadata' => ['created_via' => 'engine'],
        ]);
    }
}
