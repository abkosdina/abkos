<?php

// DEPRECATED: This advertisement-specific workflow engine has been replaced by App\Services\Workflow\WorkflowEngine.
// It is retained temporarily for backward compatibility and should not be used by new code.

namespace Modules\Advertisements\Services\Workflow;

use Illuminate\Support\Facades\Event;
use Modules\Advertisements\Adapters\AdvertisementWorkflowAdapter;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Models\Advertisement;

/**
 * Workflow Engine
 *
 * Core workflow orchestration service.
 * All workflow transitions must go through this engine.
 * This ensures consistency, audit trail, and business rule validation.
 */
class WorkflowEngine
{
    protected WorkflowStateManager $stateManager;
    protected WorkflowTransitionManager $transitionManager;
    protected AdvertisementWorkflowAdapter $adapter;
    protected array $config;

    public function __construct(
        ?WorkflowStateManager $stateManager = null,
        ?WorkflowTransitionManager $transitionManager = null,
        ?AdvertisementWorkflowAdapter $adapter = null
    ) {
        $this->stateManager = $stateManager ?? app(WorkflowStateManager::class);
        $this->transitionManager = $transitionManager ?? app(WorkflowTransitionManager::class);
        $this->adapter = $adapter ?? app(AdvertisementWorkflowAdapter::class);
        $this->config = config('advertisement-workflow');
    }

    /**
     * Execute a workflow action
     */
    public function execute(
        Advertisement $advertisement,
        string $action,
        array $payload = []
    ): bool {
        // Validate action
        if (!$this->isValidAction($action)) {
            return false;
        }

        // Check authorization
        if (!$this->authorize($advertisement, $action, $payload)) {
            return false;
        }

        // Execute the action
        $result = match ($action) {
            'submit' => $this->submitAdvertisement($advertisement, $payload),
            'approve' => $this->approveAdvertisement($advertisement, $payload),
            'reject' => $this->rejectAdvertisement($advertisement, $payload),
            'correction' => $this->requestCorrection($advertisement, $payload),
            'publish' => $this->publishAdvertisement($advertisement, $payload),
            'pause' => $this->pauseAdvertisement($advertisement, $payload),
            'resume' => $this->resumeAdvertisement($advertisement, $payload),
            'archive' => $this->archiveAdvertisement($advertisement, $payload),
            'restore' => $this->restoreAdvertisement($advertisement, $payload),
            'expire' => $this->expireAdvertisement($advertisement, $payload),
            'sold' => $this->markAsSold($advertisement, $payload),
            default => false,
        };

        return $result;
    }

    /**
     * Ensure a workflow instance exists for the advertisement.
     */
    public function ensureWorkflowInstance(Advertisement $advertisement): bool
    {
        $this->adapter->ensureWorkflowInstance($advertisement);

        return true;
    }

    /**
     * Submit advertisement for review
     */
    public function submitAdvertisement(Advertisement $advertisement, array $payload = []): bool
    {
        if (!in_array($advertisement->status, [AdvertisementStatus::Draft, AdvertisementStatus::NeedCorrection])) {
            return false;
        }

        $response = $this->adapter->submit($advertisement, array_merge($payload, ['action' => 'submit']));

        if ($response->success && ($this->config['events']['submitted'] ?? true)) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementSubmitted($advertisement));
        }

        return $response->success;
    }

    /**
     * Approve advertisement
     */
    public function approveAdvertisement(Advertisement $advertisement, array $payload = []): bool
    {
        if ($advertisement->status !== AdvertisementStatus::PendingReview) {
            return false;
        }

        $response = $this->adapter->approve($advertisement, array_merge($payload, ['action' => 'approve']));

        if ($response->success && ($this->config['events']['approved'] ?? true)) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementApproved($advertisement));
        }

        return $response->success;
    }

    /**
     * Reject advertisement
     */
    public function rejectAdvertisement(Advertisement $advertisement, array $payload = []): bool
    {
        if ($advertisement->status !== AdvertisementStatus::PendingReview) {
            return false;
        }

        $response = $this->adapter->reject($advertisement, array_merge($payload, ['action' => 'reject']));

        if ($response->success && ($this->config['events']['rejected'] ?? true)) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementRejected($advertisement));
        }

        return $response->success;
    }

    /**
     * Request correction
     */
    public function requestCorrection(Advertisement $advertisement, array $payload = []): bool
    {
        if ($advertisement->status !== AdvertisementStatus::PendingReview) {
            return false;
        }

        $response = $this->adapter->requestCorrection($advertisement, array_merge($payload, ['action' => 'correction']));

        if ($response->success && ($this->config['events']['correction_requested'] ?? true)) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementCorrectionRequested($advertisement));
        }

        return $response->success;
    }

    /**
     * Publish advertisement
     */
    public function publishAdvertisement(Advertisement $advertisement, array $payload = []): bool
    {
        if ($advertisement->status !== AdvertisementStatus::Approved) {
            return false;
        }

        $advertisement->published_at = now();
        $response = $this->adapter->publish($advertisement, array_merge($payload, ['action' => 'publish']));

        if ($response->success && ($this->config['events']['published'] ?? true)) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementPublished($advertisement));
        }

        return $response->success;
    }

    /**
     * Pause advertisement
     */
    public function pauseAdvertisement(Advertisement $advertisement, array $payload = []): bool
    {
        if ($advertisement->status !== AdvertisementStatus::Published) {
            return false;
        }

        $response = $this->adapter->pause($advertisement, array_merge($payload, ['action' => 'pause']));

        if ($response->success && ($this->config['events']['paused'] ?? true)) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementPaused($advertisement));
        }

        return $response->success;
    }

    /**
     * Resume advertisement
     */
    public function resumeAdvertisement(Advertisement $advertisement, array $payload = []): bool
    {
        if ($advertisement->status !== AdvertisementStatus::Paused) {
            return false;
        }

        $response = $this->adapter->resume($advertisement, array_merge($payload, ['action' => 'resume']));

        if ($response->success && ($this->config['events']['resumed'] ?? true)) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementResumed($advertisement));
        }

        return $response->success;
    }

    /**
     * Archive advertisement
     */
    public function archiveAdvertisement(Advertisement $advertisement, array $payload = []): bool
    {
        $archivableStates = [
            AdvertisementStatus::Published,
            AdvertisementStatus::Rejected,
            AdvertisementStatus::Expired,
            AdvertisementStatus::Sold,
        ];

        if (!in_array($advertisement->status, $archivableStates)) {
            return false;
        }

        $response = $this->adapter->archive($advertisement, array_merge($payload, ['action' => 'archive']));

        if ($response->success && ($this->config['events']['archived'] ?? true)) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementArchived($advertisement));
        }

        return $response->success;
    }

    /**
     * Restore advertisement (from Archived)
     */
    public function restoreAdvertisement(Advertisement $advertisement, array $payload = []): bool
    {
        if ($advertisement->status !== AdvertisementStatus::Archived) {
            return false;
        }

        $restoreToState = $payload['restore_to_state'] ?? AdvertisementStatus::Draft;
        $response = $this->adapter->restore($advertisement, array_merge($payload, ['action' => 'restore', 'restore_to_state' => $restoreToState]));

        if ($response->success && ($this->config['events']['restored'] ?? true)) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementRestored($advertisement));
        }

        return $response->success;
    }

    /**
     * Mark advertisement as expired
     */
    public function expireAdvertisement(Advertisement $advertisement, array $payload = []): bool
    {
        // Validate: Only Published can be Expired
        if ($advertisement->status !== AdvertisementStatus::Published) {
            return false;
        }

        // Transition to Expired
        $result = $this->transitionManager->transition(
            $advertisement,
            AdvertisementStatus::Expired,
            array_merge($payload, ['action' => 'expire'])
        );

        if ($result && $this->config['events']['expired']) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementExpired($advertisement));
        }

        return $result;
    }

    /**
     * Mark advertisement as sold
     */
    public function markAsSold(Advertisement $advertisement, array $payload = []): bool
    {
        if ($advertisement->status !== AdvertisementStatus::Published) {
            return false;
        }

        $response = $this->adapter->markAsSold($advertisement, array_merge($payload, ['action' => 'sold']));

        if ($response->success && ($this->config['events']['sold'] ?? true)) {
            Event::dispatch(new \Modules\Advertisements\Events\AdvertisementSold($advertisement));
        }

        return $response->success;
    }

    /**
     * Check if action is valid
     */
    protected function isValidAction(string $action): bool
    {
        $validActions = [
            'submit', 'approve', 'reject', 'correction', 'publish',
            'pause', 'resume', 'archive', 'restore', 'expire', 'sold'
        ];

        return in_array($action, $validActions);
    }

    /**
     * Check authorization for action
     */
    protected function authorize(Advertisement $advertisement, string $action, array $payload): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // Get required roles for this action
        $requiredRoles = $this->config['action_approval'][$action] ?? [];

        // Check if user has any of the required roles
        foreach ($requiredRoles as $requiredRole) {
            if ($requiredRole === 'owner' && $advertisement->user_id === $user->id) {
                return true;
            }
            if ($requiredRole === 'system') {
                // System actions are always allowed (cron, etc)
                return true;
            }
            if ($user->hasRole($requiredRole)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current state info
     */
    public function getCurrentStateInfo(Advertisement $advertisement): array
    {
        return [
            'state' => $advertisement->status->value,
            'label' => $this->stateManager->getStateLabel($advertisement->status),
            'description' => $this->stateManager->getStateDescription($advertisement->status),
            'is_final' => $this->stateManager->isFinalState($advertisement->status),
            'is_published' => $this->stateManager->isPublished($advertisement->status),
            'is_archived' => $this->stateManager->isArchived($advertisement->status),
            'is_searchable' => $this->stateManager->isSearchable($advertisement->status),
            'is_editable' => $this->stateManager->isEditable($advertisement->status),
            'is_deletable' => $this->stateManager->isDeletable($advertisement->status),
        ];
    }

    /**
     * Get available actions for current state
     */
    public function getAvailableActions(Advertisement $advertisement): array
    {
        return $this->transitionManager->getNextStates($advertisement);
    }
}
