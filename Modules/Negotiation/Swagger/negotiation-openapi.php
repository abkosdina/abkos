<?php

return [
    'openapi' => '3.0.0',
    'info' => [
        'title' => 'Negotiation Module API',
        'version' => '1.0.0',
        'description' => 'REST API for advertisement-based negotiation and offer workflows.',
    ],
    'paths' => [
        '/api/v1/negotiations' => [
            'get' => [
                'summary' => 'List negotiations',
                'responses' => [
                    '200' => ['description' => 'Successful response'],
                ],
            ],
        ],
        '/api/v1/advertisements/{uuid}/negotiations' => [
            'post' => [
                'summary' => 'Create negotiation for advertisement',
                'responses' => [
                    '200' => ['description' => 'Successful response'],
                ],
            ],
        ],
        '/api/v1/negotiations/{uuid}/offers' => [
            'post' => [
                'summary' => 'Create negotiation offer',
                'responses' => [
                    '200' => ['description' => 'Successful response'],
                ],
            ],
        ],
    ],
];
