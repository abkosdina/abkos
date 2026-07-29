<?php

return [
    'statuses' => [
        'draft',
        'pending',
        'submitted',
        'under_review',
        'needs_correction',
        'approved',
        'rejected',
        'suspended',
        'expired',
        'cancelled',
    ],

    'required_documents' => [
        'national_id_front',
        'national_id_back',
        'birth_certificate',
        'declaration',
        'handwritten_signature',
    ],

    'allowed_mime_types' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
    ],

    'max_document_size' => 10 * 1024 * 1024, // 10MB

    'field_validation' => [
        'national_code' => ['required' => true, 'regex' => '/^[0-9]{10}$/', 'unique' => true],
        'postal_code' => ['required' => true, 'regex' => '/^[0-9]{10}$/'],
        'birth_date' => ['required' => true, 'date' => true],
    ],

    'storage' => [
        'disk' => env('KYC_STORAGE_DISK', 'local'),
        'path' => env('KYC_STORAGE_PATH', 'kyc/documents'),
    ],

    'approval' => [
        'required_approvers' => 1,
        'allow_rejection' => true,
        'allow_correction_requests' => true,
    ],
];
