<?php

declare(strict_types=1);

$path = __DIR__ . '/../storage/app/route-list.json';
$s = file_get_contents($path);
if ($s === false) {
    fwrite(STDERR, "Cannot read $path\n");
    exit(1);
}

echo "bytes=" . strlen($s) . PHP_EOL;

json_decode($s, true);
echo "json_error=" . json_last_error() . " " . json_last_error_msg() . PHP_EOL;
