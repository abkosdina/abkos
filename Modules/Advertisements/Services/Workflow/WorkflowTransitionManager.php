<?php

namespace Modules\Advertisements\Services\Workflow;

use Illuminate\Support\Arr;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Models\AdvertisementWorkflowAudit;

/**
 * Workflow Transition Manager
 *
 * Manages workflow state transitions and validates transition rules.
 */
class WorkflowTransitionManager
{
    protected WorkflowStateManager $stateManager;

    public function __construct(WorkflowStateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    /**
     * Execute a state transition
     */
    public function transition(
        Advertisement $advertisement,
        AdvertisementStatus $toState,
        array $transitionData = []
    ): bool {
        $fromState = $advertisement->status;

        // Verify transition is allowed
        if (!$this->canTransition($advertisement, $toState)) {
            return false;
        }

        // Store old state for audit
        $oldState = $fromState->value;
        $newState = $toState->value;

        // Update advertisement status
        $advertisement->status = $toState;
        $advertisement->save();

        // Log the transition
        if (config('advertisement-workflow.audit.enabled')) {
            $this->logTransition($advertisement, $oldState, $newState, $transitionData);
        }

        return true;
    }

    /**
     * Check if a transition is allowed
     */
    public function canTransition(
        Advertisement $advertisement,
        AdvertisementStatus $toState
    ): bool {
        $currentState = $advertisement->status;

        // Check if state exists
        if (!$this->stateManager->stateExists($toState)) {
            return false;
        }

        // Check if transition is configured
        if (!$this->stateManager->canTransition($currentState, $toState)) {
            return false;
        }

        // Apply business rules
        return $this->validateTransitionRules($advertisement, $currentState, $toState);
    }

    /**
     * Validate transition business rules
     */
    protected function validateTransitionRules(
        Advertisement $advertisement,
        AdvertisementStatus $fromState,
        AdvertisementStatus $toState
    ): bool {
        // Rule: Only Draft can be submitted
        if ($toState === AdvertisementStatus::PendingReview) {
            if ($fromState !== AdvertisementStatus::Draft && $fromState !== AdvertisementStatus::NeedCorrection) {
                return false;
            }
        }

        // Rule: Only PendingReview can be Approved
        if ($toState === AdvertisementStatus::Approved) {
            if ($fromState !== AdvertisementStatus::PendingReview) {
                return false;
            }
        }

        // Rule: Only PendingReview can be Rejected
        if ($toState === AdvertisementStatus::Rejected) {
            if ($fromState !== AdvertisementStatus::PendingReview) {
                return false;
            }
        }

        // Rule: Only Approved advertisements become Published
        if ($toState === AdvertisementStatus::Published) {
            if ($fromState !== AdvertisementStatus::Approved) {
                return false;
            }
        }

        // Rule: Only Published can be Paused/Sold/Expired/Archived
        if (in_array($toState->value, ['Paused', 'Sold', 'Expired', 'Archived'])) {
            if ($fromState !== AdvertisementStatus::Published) {
                return false;
            }
        }

        return true;
    }

    /**
     * Log state transition
     */
    protected function logTransition(
        Advertisement $advertisement,
        string $fromState,
        string $toState,
        array $transitionData = []
    ): void {
        AdvertisementWorkflowAudit::create([
            'advertisement_uuid' => $advertisement->uuid,
            'old_state' => $fromState,
            'new_state' => $toState,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->roles()->first()?->name ?? 'unknown',
            'action' => $transitionData['action'] ?? 'transition',
            'reason' => $transitionData['reason'] ?? null,
            'comment' => $transitionData['comment'] ?? null,
            'ip' => request()->ip(),
            'device' => request()->userAgent(),
            'extra' => Arr::except($transitionData, ['action', 'reason', 'comment']),
        ]);
    }

    /**
     * Get transition history
     */
    public function getTransitionHistory(Advertisement $advertisement, int $limit = 50): array
    {
        return AdvertisementWorkflowAudit::where('advertisement_uuid', $advertisement->uuid)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get next possible states
     */
    public function getNextStates(Advertisement $advertisement): array
    {
        $currentState = $advertisement->status;
        $possibleStates = $this->stateManager->getTransitions($currentState);

        return array_map(fn($state) => [
            'state' => $state,
            'label' => $this->stateManager->getStateLabel($state),
            'description' => $this->stateManager->getStateDescription($state),
        ], $possibleStates);
    }

    /**
     * Check if advertisement can transition to a specific state
     */
    public function canTransitionTo(Advertisement $advertisement, string $state): bool
    {
        try {
            $toState = AdvertisementStatus::from($state);
            return $this->canTransition($advertisement, $toState);
        } catch (\ValueError) {
            return false;
        }
    }
}
