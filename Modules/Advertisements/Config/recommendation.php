<?php

return [
    'ttl_minutes' => env('AD_RECOMMENDATION_TTL', 60),

    'weights' => [
        'views' => 0.4,
        'favorites' => 1.5,
        'negotiations' => 2.0,
        'orders' => 3.0,
        'age' => 1.0, // inverse weight (recent ads score higher)
        'seller_rating' => 1.2,
    ],

    'max_scan' => 1000,
    'limit' => 50,
];
