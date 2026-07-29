<?php

return [
    'workflow_statuses' => [
        'draft',
        'active',
        'inactive',
        'archived',
    ],

    'instance_statuses' => [
        'pending',
        'in_progress',
        'waiting_approval',
        'approved',
        'rejected',
        'completed',
        'cancelled',
        'expired',
    ],

    'approval_statuses' => [
        'pending',
        'approved',
        'rejected',
        'returned',
        'cancelled',
        'expired',
    ],

    'transition_actions' => [
        'approve',
        'reject',
        'return',
        'forward',
        'cancel',
        'auto_move',
        'conditional_move',
    ],

    'action_types' => [
        'create_event',
        'send_notification',
        'send_sms',
        'generate_pdf',
        'release_escrow',
        'activate_user',
        'create_wallet_transaction',
    ],

    'assignment_types' => [
        'role',
        'permission',
        'user',
        'department',
        'team',
    ],
];
