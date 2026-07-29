<?php

return [
    'storage' => [
        'driver' => env('DOCUMENT_STORAGE_DRIVER', 'local'),
        'disk_map' => [
            'local' => env('DOCUMENT_STORAGE_LOCAL_DISK', 'public'),
            's3' => env('DOCUMENT_STORAGE_S3_DISK', 's3'),
            'minio' => env('DOCUMENT_STORAGE_MINIO_DISK', 'minio'),
            'r2' => env('DOCUMENT_STORAGE_R2_DISK', 'r2'),
        ],
    ],

    'allowed_mime_types' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'video/mp4',
        'audio/mpeg',
    ],

    'document_statuses' => [
        'uploading',
        'uploaded',
        'pending_approval',
        'approved',
        'rejected',
        'archived',
        'deleted',
        'expired',
    ],

    'visibility' => [
        'private',
        'shared',
        'workflow',
        'public',
        'internal',
    ],
];
