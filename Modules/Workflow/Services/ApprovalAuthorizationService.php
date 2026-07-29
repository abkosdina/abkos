<?php

namespace Modules\Workflow\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Workflow\Enums\ApprovalInstanceStepStatus;
use Modules\Workflow\Enums\ApprovalStatus;
use Modules\Workflow\Exceptions\ApprovalAlreadyCompletedException;
use Modules\Workflow\Exceptions\ApprovalAlreadyRejectedException;
use Modules\Workflow\Exceptions\ApprovalExpiredException;
use Modules\Workflow\Exceptions\ApprovalStepNotActiveException;
use Modules\Workflow\Exceptions\DuplicateApprovalDecisionException;
use Modules\Workflow\Exceptions\SelfApprovalNotAllowedException;
use Modules\Workflow\Exceptions\UnauthorizedApprovalException;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;
use Modules\Workflow\Interfaces\SelfApprovalRuleInterface;

class ApprovalAuthorizationService
{
    /** @var array<int, SelfApprovalRuleInterface> */
    protected array $selfApprovalRules = [];

    public function __construct(
        protected ApproverResolverRegistry $registry,
    ) {
    }

    public function registerSelfApprovalRule(SelfApprovalRuleInterface $rule): self
    {
        $this->selfApprovalRules[] = $rule;

        return $this;
    }

    public function canApprove(User $user, ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): object
    {
        return $this->authorize($user, $approvalInstance, $step, 'approve');
    }

    public function canReject(User $user, ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): object
    {
        return $this->authorize($user, $approvalInstance, $step, 'reject');
    }

    public function canReturnForCorrection(User $user, ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): object
    {
        return $this->authorize($user, $approvalInstance, $step, 'return_for_correction');
    }

    public function canDelegate(User $user, ApprovalInstance $approvalInstance, ApprovalInstanceStep $step, ?User $target = null): object
    {
        if (! $user) {
            return $this->result(false, 'unauthorized', 'User is not authenticated.');
        }

        if ($approvalInstance->status === ApprovalStatus::approved || $approvalInstance->status === ApprovalStatus::rejected || $approvalInstance->status === ApprovalStatus::returned_for_correction) {
            return $this->result(false, 'completed', 'Approval instance is already completed.');
        }

        if ($step->status !== ApprovalInstanceStepStatus::active) {
            return $this->result(false, 'inactive_step', 'Approval step is not active.');
        }

        if (! $this->isEligibleApprover($user, $approvalInstance, $step)->authorized) {
            return $this->result(false, 'ineligible', 'User is not eligible to delegate this approval.');
        }

        if (! $target) {
            return $this->result(false, 'missing_target', 'Delegation target is required.');
        }

        if (! $this->isEligibleApprover($target, $approvalInstance, $step)->authorized) {
            return $this->result(false, 'ineligible_target', 'Delegation target is not eligible.');
        }

        if ($this->hasDuplicateDecision($user, $approvalInstance, $step)) {
            return $this->result(false, 'duplicate', 'User already decided on this step.');
        }

        return $this->result(true, 'authorized', 'Delegation authorized.');
    }

    public function isEligibleApprover(User $user, ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): object
    {
        if (! $user) {
            return $this->result(false, 'unauthorized', 'User is not authenticated.');
        }

        if ($this->isSuspended($user)) {
            return $this->result(false, 'suspended', 'User is suspended.');
        }

        if ($this->isBanned($user)) {
            return $this->result(false, 'banned', 'User is permanently banned.');
        }

        if ($this->hasDuplicateDecision($user, $approvalInstance, $step)) {
            return $this->result(false, 'duplicate', 'User already decided on this step.');
        }

        $approvalStep = $step->approvalStep;
        if (! $approvalStep) {
            return $this->result(false, 'missing_step', 'Approval step could not be resolved.');
        }

        $eligibleUsers = $this->resolveEligibleUsers($approvalInstance, $step);
        $isEligible = $eligibleUsers->contains(fn (User $eligibleUser) => $eligibleUser->id === $user->id);

        if (! $isEligible) {
            return $this->result(false, 'ineligible', 'User is not eligible for this approval step.');
        }

        foreach ($this->selfApprovalRules as $rule) {
            if ($rule->shouldBlock($user, $approvalInstance, $step)) {
                return $this->result(false, 'self_approval', $rule->getReason());
            }
        }

        return $this->result(true, 'authorized', 'User is eligible to approve.');
    }

    protected function authorize(User $user, ApprovalInstance $approvalInstance, ApprovalInstanceStep $step, string $action): object
    {
        if (! $user) {
            return $this->result(false, 'unauthorized', 'User is not authenticated.');
        }

        if ($approvalInstance->status === ApprovalStatus::approved || $approvalInstance->status === ApprovalStatus::rejected || $approvalInstance->status === ApprovalStatus::returned_for_correction) {
            return $this->result(false, 'completed', 'Approval instance is already completed.');
        }

        if ($step->status !== ApprovalInstanceStepStatus::active) {
            return $this->result(false, 'inactive_step', 'Approval step is not active.');
        }

        if ($this->isExpired($approvalInstance, $step)) {
            return $this->result(false, 'expired', 'Approval step has expired.');
        }

        $eligibility = $this->isEligibleApprover($user, $approvalInstance, $step);
        if (! $eligibility->authorized) {
            return $eligibility;
        }

        return $this->result(true, 'authorized', 'Authorization granted.');
    }

    protected function resolveEligibleUsers(ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): Collection
    {
        $approvalStep = $step->approvalStep;
        if (! $approvalStep) {
            return new Collection();
        }

        if ($approvalStep->required_user_id) {
            $user = User::query()->find($approvalStep->required_user_id);
            return $user ? new Collection([$user]) : new Collection();
        }

        return $this->registry->resolve($approvalInstance, $step);
    }

    protected function hasDuplicateDecision(User $user, ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): bool
    {
        return ApprovalDecision::query()
            ->where('approval_instance_id', $approvalInstance->id)
            ->where('approval_instance_step_id', $step->id)
            ->where('approver_id', $user->id)
            ->exists();
    }

    protected function isSuspended(User $user): bool
    {
        return (bool) data_get($user, 'is_suspended', false);
    }

    protected function isBanned(User $user): bool
    {
        return (bool) data_get($user, 'is_banned', false);
    }

    protected function isExpired(ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): bool
    {
        $approvalStep = $step->approvalStep;
        if (! $approvalStep || ! $approvalStep->expires_after_minutes) {
            return false;
        }

        return $step->started_at && $step->started_at->diffInMinutes(now()) > $approvalStep->expires_after_minutes;
    }

    protected function result(bool $authorized, string $code, string $message): object
    {
        return (object) [
            'authorized' => $authorized,
            'code' => $code,
            'message' => $message,
        ];
    }
}
