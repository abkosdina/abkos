<?php

return [
    'limits' => [
        // Maximum number of active (non-archived) advertisements a user may have at the same time.
        'active_per_user' => env('ADVERTISEMENT_ACTIVE_ADS_LIMIT', 10),

        // Maximum number of advertisements a user may create in a single calendar day.
        'daily_creation_per_user' => env('ADVERTISEMENT_DAILY_CREATION_LIMIT', 5),
    ],
];
