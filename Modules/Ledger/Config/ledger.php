<?php

return [
    'transaction_types' => [
        'deposit',
        'withdraw',
        'escrow_lock',
        'escrow_release',
        'commission',
        'refund',
        'penalty',
        'transfer',
        'adjustment',
        'settlement',
    ],

    'statuses' => [
        'pending',
        'completed',
        'cancelled',
        'failed',
        'reversed',
    ],

    'account_statuses' => [
        'active',
        'inactive',
        'suspended',
        'closed',
    ],
];
