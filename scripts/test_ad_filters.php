<?php
// Usage: php test_ad_filters.php "param1=val&param2[]=a&param2[]=b"
if ($argc < 2) {
    echo "Usage: php test_ad_filters.php \"query_string\"\n";
    exit(1);
}
$query = $argv[1];
$url = "http://127.0.0.1:8000/api/advertisements?" . $query;
$options = [
    'http' => [
        'method' => 'GET',
        'header' => "Accept: application/json\r\n",
        'timeout' => 10,
    ],
];
$ctx = stream_context_create($options);
$result = @file_get_contents($url, false, $ctx);
if ($result === false) {
    echo "REQUEST FAILED: $url\n";
    // try to get last warning
    $err = error_get_last();
    if ($err) echo $err['message'] . "\n";
    exit(2);
}
$data = json_decode($result, true);
if ($data === null) {
    echo "INVALID JSON RESPONSE\n";
    echo $result . "\n";
    exit(3);
}
$items = $data['data'] ?? [];
$meta = $data['meta'] ?? [];
printf("URL: %s\n", $url);
printf("Returned items: %d\n", count($items));
if (!empty($meta)) {
    printf("Meta: current_page=%s last_page=%s per_page=%s total=%s\n",
        $meta['current_page'] ?? '-', $meta['last_page'] ?? '-', $meta['per_page'] ?? '-', $meta['total'] ?? '-');
}
if (!empty($items)) {
    $first = $items[0];
    $title = $first['title'] ?? ($first['bank_name'] ?? 'n/a');
    printf("First item id=%s title=%s\n", $first['id'] ?? 'n/a', $title);
}
echo "---\n";
