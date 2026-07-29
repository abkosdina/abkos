<?php

namespace App\Services\Workflow;

use App\Models\WorkflowInstance;
use App\Models\WorkflowTransition;
use App\Models\User;

/**
 * WorkflowAuthorizationService
 * 
 * Handles authorization checks for workflow transitions.
 * Validates that the current user has permission to execute a transition.
 */
class WorkflowAuthorizationService
{
    /**
     * Check if user is authorized to execute a transition
     */
    public function authorize(?User $user, WorkflowTransition $transition, WorkflowInstance $instance): bool
    {
        if (!$user) {
            throw new \Exception("User must be authenticated to perform workflow transitions");
        }

        // Check if transition requires a specific role
        $requiredRoles = $transition->getRequiredRoles();
        if (!empty($requiredRoles)) {
            if (!$user->hasAnyRole($requiredRoles)) {
                throw new \Exception(
                    "User does not have required role(s): " . implode(', ', $requiredRoles)
                );
            }
        }

        // Check if transition requires a specific permission
        $requiredPermissions = $transition->getRequiredPermissions();
        if (!empty($requiredPermissions)) {
            if (!$user->hasAnyPermission($requiredPermissions)) {
                throw new \Exception(
                    "User does not have required permission(s): " . implode(', ', $requiredPermissions)
                );
            }
        }

        // Check entity-level policy (if it's an Advertisement, check AdvertisementPolicy, etc.)
        // This is deferred to Phase 2 (Policy System integration)

        return true;
    }

    /**
     * Check if user can view a workflow instance
     */
    public function canView(User $user, WorkflowInstance $instance): bool
    {
        // Allow admins to view any workflow
        if ($user->hasRole('admin')) {
            return true;
        }

        // Allow the entity owner to view their workflow
        $entity = $instance->getEntity();
        if ($entity && isset($entity->user_id) && $entity->user_id === $user->id) {
            return true;
        }

        // Allow operators to view workflows
        if ($user->hasRole(['operator', 'senior-operator'])) {
            return true;
        }

        return false;
    }
}
