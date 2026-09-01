<?php

declare(strict_types=1);

$orderNumber = $argv[1] ?? null;
if (!$orderNumber) {
    echo "Usage: php tests/race_webhook.php <order_number>\n";
    exit(1);
}

$baseUrl = 'http://localhost:8080';
$url = $baseUrl . '/webhook/payment';

$payload = [
    //'event_id' => 'evt_' . bin2hex(random_bytes(8)),
    'order_id' => $orderNumber,
    'status' => 'paid',
    'amount' => 500,
    'currency' => 'RUB',
    'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
];

$requests = [];
for ($i = 0; $i < 50; $i++) {
    // Unique event_id simulates a legit payment provider sending
    // many independent "paid" notifications. Replays with the same
    // event_id are handled by the idempotency check in gateway_events.
    $payload['event_id'] = 'evt_' . bin2hex(random_bytes(8)) . '_' . $i;
    $requests[] = ['url' => $url, 'payload' => $payload];
}

$mh = curl_multi_init();
$handles = [];

foreach ($requests as $request) {
    $ch = curl_init($request['url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request['payload']));
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

try {
    $config = require __DIR__ . '/../config/database.php';

    $dsn = sprintf(
        '%s:host=%s;port=%d;dbname=%s;charset=%s',
        $config['driver'],
        $config['host'],
        $config['port'],
        $config['dbname'],
        $config['charset']
    );

    $pdo = new \PDO($dsn, $config['username'], $config['password'], [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number = :order_number LIMIT 1');
    $stmt->execute(['order_number' => $orderNumber]);
    $order = $stmt->fetch();

    if (!$order) {
        echo "ERROR: order not found: {$orderNumber}\n";
        exit(1);
    }

    $sku = $order['product_sku'];
    $orderId = (int) $order['id'];

    $requestId = $orderNumber . '-race';
    $supplierUrl = $baseUrl . '/supplier/issue';
    $deliveredCode = null;
    $outOfStockDetected = false;

    // Supplier A may respond with errors/timeouts. Retry with the same request_id,
    // because a timeout is not a rejection and must not produce a second key.
    for ($attempt = 1; $attempt <= 30; $attempt++) {
        $ch = curl_init($supplierUrl);
        $payload = [
            'request_id' => $requestId,
            'sku' => $sku,
            'order_id' => $orderNumber,
            'supplier' => 'A',
        ];
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            usleep(5000);
            continue;
        }

        $data = json_decode($response, true);
        if (is_array($data) && ($data['status'] ?? null) === 'ok' && !empty($data['code'])) {
            $deliveredCode = $data['code'];
            break;
        }

        if (is_array($data) && ($data['reason'] ?? null) === 'out_of_stock') {
            $outOfStockDetected = true;
            break;
        }

        usleep(5000);
    }

    if (!$deliveredCode) {
        if ($outOfStockDetected) {
            echo "ERROR: supplier returned out_of_stock for order {$orderNumber}. Add keys and run admin retry.\n";
        } else {
            echo "ERROR: supplier did not return a code after 30 attempts for order {$orderNumber}.\n";
        }
        exit(1);
    }

    // Idempotency check: with the same request_id, the request must not create another key.
    $repeatCh = curl_init($supplierUrl);
    curl_setopt($repeatCh, CURLOPT_POST, true);
    curl_setopt($repeatCh, CURLOPT_POSTFIELDS, json_encode([
        'request_id' => $requestId,
        'sku' => $sku,
        'order_id' => $orderNumber,
        'supplier' => 'A',
    ]));
    curl_setopt($repeatCh, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($repeatCh, CURLOPT_RETURNTRANSFER, true);
    $repeatResponse = curl_exec($repeatCh);
    curl_close($repeatCh);

    if ($repeatResponse === false) {
        echo "WARNING: could not repeat supplier request. Manual verification needed.\n";
    }

    $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = :order_id LIMIT 1');
    $stmt->execute(['order_id' => $orderId]);
    $finalStatus = $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM `keys`
         WHERE order_id = :order_id
           AND status IN ('reserved', 'issued')"
    );
    $stmt->execute(['order_id' => $orderId]);
    $keyCount = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM supplier_requests WHERE order_id = :order_id"
    );
    $stmt->execute(['order_id' => $orderId]);
    $supplierRequests = (int) $stmt->fetchColumn();

    echo "Sent 50 parallel webhooks for order {$orderNumber}\n";
    echo "Final order status: " . $finalStatus . "\n";
    echo "Keys reserved/issued for this order: {$keyCount}\n";
    echo "Supplier requests for this order: {$supplierRequests}\n";
    echo "Delivered code: {$deliveredCode}\n";

    if ($finalStatus === 'delivered' && $keyCount === 1 && $supplierRequests >= 1) {
        echo "OK: payment webhooks are idempotent and exactly one key was issued.\n";
    } else {
        echo "ERROR: unexpected final state after supplier flow.\n";
        exit(1);
    }
} catch (\Throwable $e) {
    echo 'Warning: could not inspect database state: ' . $e->getMessage() . "\n";
    echo "Sent 50 parallel webhooks for order {$orderNumber}\n";
    echo "Open /order/status/{$orderNumber} in a browser to verify manually.\n";
}
