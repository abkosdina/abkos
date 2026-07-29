<?php
$path = 'resources/views/ads/ads.blade.php';
$text = file_get_contents($path);

$text = preg_replace('/<!--.*?-->/s', '', $text);
$text = preg_replace('/\/\*.*?\*\//s', '', $text);
$text = preg_replace('/(?m)^\s*\/\/.*$/', '', $text);
$text = preg_replace('/\n{3,}/', "\n\n", $text);
$text = str_replace(["\r\n", "\r"], "\n", $text);
$text = preg_replace('/\n{3,}/', "\n\n", $text);
$text = trim($text) . "\n";
file_put_contents($path, $text);
echo "refactored\n";
