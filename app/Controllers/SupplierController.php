<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Services\SupplierService;

final class SupplierController extends Controller
{
    public function issue(): void
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $this->json(['status' => 'error', 'reason' => 'invalid_json'], 400);
        }

        $requestId = $data['request_id'] ?? null;
        $sku = $data['sku'] ?? null;
        $orderNumber = $data['order_id'] ?? null;
        $provider = strtoupper((string) ($data['supplier'] ?? 'A'));

        if (!$requestId || !$sku || !$orderNumber) {
            $this->json(['status' => 'error', 'reason' => 'missing_fields'], 400);
        }

        if (!in_array($provider, ['A', 'B'], true)) {
            $this->json(['status' => 'error', 'reason' => 'invalid_supplier'], 400);
        }

        $order = Order::findByOrderNumber((string) $orderNumber);
        if (!$order) {
            $this->json(['status' => 'error', 'reason' => 'order_not_found'], 404);
        }

        $code = SupplierService::issue(
            $requestId,
            $sku,
            (int) $order['id'],
            (string) $orderNumber,
            $provider
        );

        if ($code !== null) {
            if ($code === 'out_of_stock') {
                $this->json([
                    'status' => 'error',
                    'reason' => 'out_of_stock',
                ], 503);
            } else {
                $this->json([
                    'status' => 'ok',
                    'request_id' => $requestId,
                    'code' => $code,
                ]);
            }
        } else {
            $this->json([
                'status' => 'error',
                'reason' => 'При получении кода возникла ошибка. Попробуйте еще раз или обратитесь к администратору сайта.',
            ], 503);
        }
    }
}
