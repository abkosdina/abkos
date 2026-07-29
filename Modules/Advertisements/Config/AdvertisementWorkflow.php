<?php

/**
 * Advertisement Workflow Configuration
 *
 * This configuration defines the complete workflow for advertisements.
 * It is NOT hardcoded - states and transitions are managed dynamically.
 *
 * All workflow logic should go through the Workflow Engine.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Workflow States
    |--------------------------------------------------------------------------
    |
    | Define all possible states for advertisements.
    | These should match the AdvertisementStatus enum.
    */
    'states' => [
        'Draft' => [
            'label' => 'Draft',
            'description' => 'Advertisement is in draft status',
            'is_final' => false,
            'is_published' => false,
            'is_archived' => false,
            'is_searchable' => false,
            'is_editable' => true,
            'is_deletable' => true,
        ],
        'PendingReview' => [
            'label' => 'Pending Review',
            'description' => 'Advertisement is waiting for review',
            'is_final' => false,
            'is_published' => false,
            'is_archived' => false,
            'is_searchable' => false,
            'is_editable' => false,
            'is_deletable' => false,
        ],
        'NeedCorrection' => [
            'label' => 'Need Correction',
            'description' => 'Advertisement requires corrections',
            'is_final' => false,
            'is_published' => false,
            'is_archived' => false,
            'is_searchable' => false,
            'is_editable' => true,
            'is_deletable' => false,
        ],
        'Rejected' => [
            'label' => 'Rejected',
            'description' => 'Advertisement was rejected',
            'is_final' => true,
            'is_published' => false,
            'is_archived' => false,
            'is_searchable' => false,
            'is_editable' => false,
            'is_deletable' => false,
        ],
        'Approved' => [
            'label' => 'Approved',
            'description' => 'Advertisement is approved',
            'is_final' => false,
            'is_published' => false,
            'is_archived' => false,
            'is_searchable' => false,
            'is_editable' => false,
            'is_deletable' => false,
        ],
        'Published' => [
            'label' => 'Published',
            'description' => 'Advertisement is published and visible',
            'is_final' => false,
            'is_published' => true,
            'is_archived' => false,
            'is_searchable' => true,
            'is_editable' => false,
            'is_deletable' => false,
        ],
        'Paused' => [
            'label' => 'Paused',
            'description' => 'Advertisement is paused',
            'is_final' => false,
            'is_published' => false,
            'is_archived' => false,
            'is_searchable' => false,
            'is_editable' => false,
            'is_deletable' => false,
        ],
        'Expired' => [
            'label' => 'Expired',
            'description' => 'Advertisement has expired',
            'is_final' => true,
            'is_published' => false,
            'is_archived' => false,
            'is_searchable' => false,
            'is_editable' => false,
            'is_deletable' => false,
        ],
        'Sold' => [
            'label' => 'Sold',
            'description' => 'Advertisement item is sold',
            'is_final' => true,
            'is_published' => false,
            'is_archived' => false,
            'is_searchable' => false,
            'is_editable' => false,
            'is_deletable' => false,
        ],
        'Archived' => [
            'label' => 'Archived',
            'description' => 'Advertisement is archived and read-only',
            'is_final' => true,
            'is_published' => false,
            'is_archived' => true,
            'is_searchable' => false,
            'is_editable' => false,
            'is_deletable' => false,
        ],
        'Deleted' => [
            'label' => 'Deleted',
            'description' => 'Advertisement is deleted',
            'is_final' => true,
            'is_published' => false,
            'is_archived' => false,
            'is_searchable' => false,
            'is_editable' => false,
            'is_deletable' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflow Transitions
    |--------------------------------------------------------------------------
    |
    | Define all allowed transitions between states.
    | Format: 'from_state' => ['to_state1', 'to_state2', ...]
    */
    'transitions' => [
        'Draft' => ['PendingReview'],
        'PendingReview' => ['Approved', 'NeedCorrection', 'Rejected'],
        'NeedCorrection' => ['PendingReview'],
        'Rejected' => ['Archived'],
        'Approved' => ['Published'],
        'Published' => ['Paused', 'Sold', 'Expired', 'Archived'],
        'Paused' => ['Published'],
        'Expired' => ['Archived'],
        'Sold' => ['Archived'],
        'Archived' => [],  // Final state - no transitions
        'Deleted' => [],   // Final state - no transitions
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Permissions Matrix
    |--------------------------------------------------------------------------
    |
    | Define which actions each role can perform on advertisements.
    */
    'role_permissions' => [
        'user' => [
            'create-advertisement',
            'update-own-advertisement',
            'delete-own-advertisement',
            'submit-advertisement',
            'pause-advertisement',
            'resume-advertisement',
            'archive-advertisement',
            'view-own-advertisement',
        ],
        'operator' => [
            'view-pending-advertisements',
            'approve-advertisement',
            'reject-advertisement',
            'request-correction',
            'hide-advertisement',
            'view-reports',
        ],
        'senior-operator' => [
            'view-pending-advertisements',
            'approve-advertisement',
            'reject-advertisement',
            'request-correction',
            'hide-advertisement',
            'view-reports',
            'restore-advertisement',
            'feature-advertisement',
        ],
        'moderator' => [
            'suspend-advertisement',
            'remove-advertisement',
            'investigate-reports',
            'manage-violations',
        ],
        'admin' => [
            'manage-all-advertisements',
            'force-archive',
            'force-publish',
            'force-pause',
            'change-owner',
            'manage-priorities',
        ],
        'super-admin' => [
            'manage-workflow',
            'manage-permissions',
            'manage-roles',
            'manage-advertisement-settings',
            'manage-workflow-templates',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Approval Requirements
    |--------------------------------------------------------------------------
    |
    | Define which roles can perform specific workflow actions.
    */
    'action_approval' => [
        'submit' => ['owner'],
        'approve' => ['operator', 'senior-operator', 'admin'],
        'reject' => ['operator', 'senior-operator', 'admin'],
        'correction' => ['operator', 'senior-operator', 'admin'],
        'publish' => ['operator', 'senior-operator', 'admin'],
        'pause' => ['owner', 'operator', 'senior-operator', 'admin'],
        'resume' => ['owner', 'operator', 'senior-operator', 'admin'],
        'archive' => ['owner', 'operator', 'senior-operator', 'admin'],
        'restore' => ['senior-operator', 'admin'],
        'expire' => ['system', 'admin'],
        'sold' => ['owner', 'admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Edit Rules
    |--------------------------------------------------------------------------
    |
    | Define which fields are editable based on negotiation/order status.
    */
    'edit_rules' => [
        'before_negotiation' => [
            'title',
            'description',
            'price',
            'images',
            'documents',
            'visibility',
            'negotiable',
            'priority',
        ],
        'after_negotiation_locked' => [
            'loan_amount',
            'bank_id',
            'loan_plan',
            'loan_type',
            'installments',
            'interest_rate',
            'branch',
        ],
        'after_order_read_only' => [
            'financial_fields',
            'transfer_conditions',
            'loan_information',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Delete Rules
    |--------------------------------------------------------------------------
    |
    | Define delete behavior based on advertisement state.
    */
    'delete_rules' => [
        'no_negotiation' => 'soft_delete',
        'negotiation_exists' => 'archive_only',
        'order_exists' => 'archive_only',
        'never_hard_delete' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    |
    | Configure audit logging behavior.
    */
    'audit' => [
        'enabled' => true,
        'log_state_transitions' => true,
        'log_approvals' => true,
        'log_rejections' => true,
        'log_corrections' => true,
        'track_ip' => true,
        'track_user_agent' => true,
        'track_device' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which notifications to send for workflow events.
    */
    'notifications' => [
        'submitted' => true,
        'approved' => true,
        'rejected' => true,
        'correction_requested' => true,
        'published' => true,
        'paused' => true,
        'archived' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which events to dispatch for workflow transitions.
    */
    'events' => [
        'submitted' => true,
        'approved' => true,
        'rejected' => true,
        'correction_requested' => true,
        'published' => true,
        'paused' => true,
        'resumed' => true,
        'archived' => true,
        'restored' => true,
        'expired' => true,
        'sold' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for workflow configuration.
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'advertisement_workflow',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Feature toggles for workflow functionality.
    */
    'features' => [
        'correction_requests' => true,
        'rejection_reasons' => true,
        'approval_comments' => true,
        'workflow_templates' => true,
        'bulk_actions' => true,
        'workflow_analytics' => true,
    ],
];
