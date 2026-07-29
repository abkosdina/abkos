<?php

function postJson($url, $data, $headers = []) {
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => array_merge(["Content-Type: application/json"], $headers),
            'content' => json_encode($data),
            'ignore_errors' => true,
            'timeout' => 30,
        ],
    ];

    $context  = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    $respHeaders = $http_response_header ?? [];
    return ['body' => $result, 'headers' => $respHeaders];
}

$base = 'http://127.0.0.1:8000';
$mobile = $argv[1] ?? '09134576502';

echo "Requesting OTP for: $mobile\n";
$r = postJson($base . '/api/v1/auth/otp/request', ['mobile' => $mobile]);
echo "OTP request response headers:\n" . implode("\n", $r['headers']) . "\n";
echo "OTP request body:\n" . $r['body'] . "\n";

// wait a moment for log to be written
sleep(1);
$logFile = __DIR__ . '/../storage/logs/laravel.log';
$otp = null;
if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    for ($i = count($lines)-1; $i >= 0; $i--) {
        if (strpos($lines[$i], 'OTP generated') !== false && strpos($lines[$i], $mobile) !== false) {
            // parse JSON at end of line
            $pos = strrpos($lines[$i], '{');
            if ($pos !== false) {
                $json = substr($lines[$i], $pos);
                $data = json_decode($json, true);
                if (isset($data['otp'])) {
                    $otp = $data['otp'];
                    break;
                }
            }
        }
    }
}

if (! $otp) {
    echo "Could not find OTP in log for $mobile\n";
    exit(2);
}

echo "Found OTP: $otp\n";

echo "Verifying OTP...\n";
$verify = postJson($base . '/api/v1/auth/otp/verify', ['mobile' => $mobile, 'otp' => $otp]);
echo "Verify response headers:\n" . implode("\n", $verify['headers']) . "\n";
echo "Verify body:\n" . $verify['body'] . "\n";

$verifyBody = json_decode($verify['body'], true);
if (! isset($verifyBody['auth']['token'])) {
    echo "No token in verify response.\n";
    exit(3);
}

$token = $verifyBody['auth']['token'];
echo "Obtained token: $token\n";

// prepare advertisement payload
$payload = [
    'title' => 'تست ثبت آگهی',
    'description' => 'تست از API',
    'province_id' => 1,
    'city_id' => 1,
    'visibility' => 'Public',
    'priority' => 0,
    'loan_product_id' => 1,
    'loan_offer' => [
        'bank_id' => 1,
        'loan_plan_id' => 1,
        'loan_amount' => 700000000,
        'sale_price' => 430000000,
        'interest_rate' => 23.0,
        'installment_count' => 12,
        'monthly_installment' => intval(round(430000000 / 12)),
        'loan_type_id' => 1,
    ],
];

echo "Posting advertisement with token...\n";
$post = postJson($base . '/api/advertisements', $payload, ["Authorization: Bearer $token", "Accept: application/json"]);

echo "Post response headers:\n" . implode("\n", $post['headers']) . "\n";
echo "Post body:\n" . $post['body'] . "\n";

file_put_contents(__DIR__ . '/last_ad_response.json', $post['body']);

echo "Done. Saved response to scripts/last_ad_response.json\n";
