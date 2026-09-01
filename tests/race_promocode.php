<?php

declare(strict_types=1);

require __DIR__ . '/../autoload.php';

$promocode = $argv[1] ?? 'LIMIT3';
$sku = $argv[2] ?? 'STEAM-TOPUP-500';

$url = 'http://localhost:8080/orders';
$requests = [];

for ($i = 0; $i < 10; $i++) {
    $requests[] = ['sku' => $sku, 'promocode' => $promocode];
}

$mh = curl_multi_init();
$handles = [];

foreach ($requests as $payload) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}

do {
    $status = curl_multi_exec($mh, $active);
    curl_multi_select($mh);
} while ($active && $status === CURLM_OK);

foreach ($handles as $ch) {
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

echo "Sent 10 parallel order requests with promocode {$promocode}\n";
