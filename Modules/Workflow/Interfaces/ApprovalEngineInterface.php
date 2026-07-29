<?php

namespace Modules\Workflow\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Workflow\Models\ApprovalDefinition;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Enums\ApprovalStatus;

interface ApprovalEngineInterface
{
    public function start(WorkflowInstance $workflowInstance, ApprovalDefinition $approvalDefinition): ApprovalInstance;

    public function approve(ApprovalInstance $approvalInstance, User $user, array $payload = []): ApprovalDecision;

    public function reject(ApprovalInstance $approvalInstance, User $user, string $reason, ?string $comment = null, array $payload = []): ApprovalDecision;

    public function returnForCorrection(ApprovalInstance $approvalInstance, User $user, string $reason, ?string $comment = null, array $payload = []): ApprovalDecision;

    public function getStatus(ApprovalInstance $approvalInstance): ApprovalStatus;

    public function getPendingApprovals(): Collection;

    public function isApproved(ApprovalInstance $approvalInstance): bool;

    public function isRejected(ApprovalInstance $approvalInstance): bool;

    public function isCompleted(ApprovalInstance $approvalInstance): bool;
}
