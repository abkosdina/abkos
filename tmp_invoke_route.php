<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/v1/locations/provinces/8/cities', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
$response = $app->handle($request);
echo "Status: " . $response->getStatusCode() . PHP_EOL;
foreach ($response->headers->all() as $k => $v) {
	echo $k . ': ' . implode(', ', $v) . PHP_EOL;
}
echo "Body:\n" . (string) $response->getContent() . PHP_EOL;
