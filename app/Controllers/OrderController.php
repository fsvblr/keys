<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Key;
use App\Models\Order;
use App\Services\OrderService;

final class OrderController extends Controller
{
    public function create(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $sku = $input['sku'] ?? null;
        $promocode = $input['promocode'] ?? null;

        if (!$sku) {
            $this->json(['error' => 'sku is required'], 400);
        }

        try {
            $result = OrderService::createOrder($sku, $promocode);
            $this->json($result);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function status(string $orderNumber): void
    {
        $order = Order::findByOrderNumber($orderNumber);
        if (!$order) {
            http_response_code(404);
            echo 'Order not found';
            return;
        }

        $keyCode = null;
        if ($order['status'] === 'delivered') {
            $keyCode = Key::getIssuedCodeForOrder((int) $order['id']);
        }

        $this->view('order/status', [
            'order' => $order,
            'keyCode' => $keyCode,
        ]);
    }

    public function simulatePayment(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $orderNumber = $input['order_number'] ?? null;
        $status = $input['status'] ?? 'paid';

        if (!$orderNumber) {
            $this->json(['error' => 'order_number is required'], 400);
        }

        $order = Order::findByOrderNumber((string) $orderNumber);
        if (!$order) {
            $this->json(['error' => 'Order not found'], 404);
        }

        $amount = (float) $order['final_amount'];
        $currency = $order['currency'];

        $payload = [
            'event_id' => 'evt_' . uniqid('', true),
            'order_id' => $orderNumber,
            'status' => $status,
            'amount' => $amount,
            'currency' => $currency,
            'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ];

        $url = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8080') . '/webhook/payment';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->json(['ok' => true, 'sent' => $payload, 'http_code' => $httpCode]);
    }
}
