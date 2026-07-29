<?php

namespace Modules\Advertisements\Services\Workflow;

use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Models\Advertisement;

/**
 * Workflow State Manager
 *
 * Manages workflow states, transitions, and state-related operations.
 * Configuration-driven, never hardcoded.
 */
class WorkflowStateManager
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('advertisement-workflow');
    }

    /**
     * Get all available states
     */
    public function getStates(): array
    {
        return $this->config['states'];
    }

    /**
     * Get state configuration
     */
    public function getState(AdvertisementStatus|string $state): array
    {
        $stateName = $state instanceof AdvertisementStatus ? $state->value : $state;
        return $this->config['states'][$stateName] ?? [];
    }

    /**
     * Get state label
     */
    public function getStateLabel(AdvertisementStatus|string $state): string
    {
        $stateConfig = $this->getState($state);
        return $stateConfig['label'] ?? 'Unknown';
    }

    /**
     * Get state description
     */
    public function getStateDescription(AdvertisementStatus|string $state): string
    {
        $stateConfig = $this->getState($state);
        return $stateConfig['description'] ?? '';
    }

    /**
     * Check if state is final
     */
    public function isFinalState(AdvertisementStatus|string $state): bool
    {
        $stateConfig = $this->getState($state);
        return $stateConfig['is_final'] ?? false;
    }

    /**
     * Check if state is published
     */
    public function isPublished(AdvertisementStatus|string $state): bool
    {
        $stateConfig = $this->getState($state);
        return $stateConfig['is_published'] ?? false;
    }

    /**
     * Check if state is archived
     */
    public function isArchived(AdvertisementStatus|string $state): bool
    {
        $stateConfig = $this->getState($state);
        return $stateConfig['is_archived'] ?? false;
    }

    /**
     * Check if state is searchable
     */
    public function isSearchable(AdvertisementStatus|string $state): bool
    {
        $stateConfig = $this->getState($state);
        return $stateConfig['is_searchable'] ?? false;
    }

    /**
     * Check if state is editable
     */
    public function isEditable(AdvertisementStatus|string $state): bool
    {
        $stateConfig = $this->getState($state);
        return $stateConfig['is_editable'] ?? false;
    }

    /**
     * Check if state is deletable
     */
    public function isDeletable(AdvertisementStatus|string $state): bool
    {
        $stateConfig = $this->getState($state);
        return $stateConfig['is_deletable'] ?? false;
    }

    /**
     * Get all available transitions for a state
     */
    public function getTransitions(AdvertisementStatus|string $state): array
    {
        $stateName = $state instanceof AdvertisementStatus ? $state->value : $state;
        return $this->config['transitions'][$stateName] ?? [];
    }

    /**
     * Check if transition is allowed
     */
    public function canTransition(
        AdvertisementStatus|string $fromState,
        AdvertisementStatus|string $toState
    ): bool {
        $from = $fromState instanceof AdvertisementStatus ? $fromState->value : $fromState;
        $to = $toState instanceof AdvertisementStatus ? $toState->value : $toState;

        $allowedTransitions = $this->getTransitions($from);
        return in_array($to, $allowedTransitions);
    }

    /**
     * Get all valid states
     */
    public function getValidStates(): array
    {
        return array_keys($this->config['states']);
    }

    /**
     * Verify state exists
     */
    public function stateExists(AdvertisementStatus|string $state): bool
    {
        $stateName = $state instanceof AdvertisementStatus ? $state->value : $state;
        return isset($this->config['states'][$stateName]);
    }

    /**
     * Get state machine graph (for visualization)
     */
    public function getStateMachineGraph(): array
    {
        return $this->config['transitions'];
    }
}
