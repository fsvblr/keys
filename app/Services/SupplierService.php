<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Key;
use App\Models\Order;

final class SupplierService
{
    public static function issue(string $requestId, string $sku, int $orderId, string $orderNumber, string $provider = 'A'): ?string
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :order_id FOR UPDATE');
            $stmt->execute(['order_id' => $orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                $pdo->rollBack();
                return null;
            }

            if ($order['status'] === 'delivered') {
                $stmt = $pdo->prepare('SELECT code FROM supplier_requests WHERE order_id = :order_id AND status = :status LIMIT 1');
                $stmt->execute(['order_id' => $orderId, 'status' => 'success']);
                $code = $stmt->fetchColumn();

                if (!$code) {
                    $stmt = $pdo->prepare('SELECT code FROM `keys` WHERE order_id = :order_id AND status = :status LIMIT 1');
                    $stmt->execute(['order_id' => $orderId, 'status' => 'issued']);
                    $code = $stmt->fetchColumn();
                }

                $pdo->commit();
                return $code ?: null;
            }

            if ($order['status'] === 'payment_failed') {
                $pdo->commit();
                return null;
            }

            $stmt = $pdo->prepare('SELECT * FROM supplier_requests WHERE request_id = :request_id FOR UPDATE');
            $stmt->execute(['request_id' => $requestId]);
            $request = $stmt->fetch();

            if (!$request) {
                $stmt = $pdo->prepare(
                    'INSERT INTO supplier_requests (request_id, order_id, sku, status, attempts, created_at, updated_at)
                     VALUES (:request_id, :order_id, :sku, :status, 0, NOW(), NOW())'
                );
                $stmt->execute([
                    'request_id' => $requestId,
                    'order_id' => $orderId,
                    'sku' => $sku,
                    'status' => 'pending',
                ]);

                $stmt = $pdo->prepare('SELECT * FROM supplier_requests WHERE request_id = :request_id');
                $stmt->execute(['request_id' => $requestId]);
                $request = $stmt->fetch();
            }

            if ($request['status'] === 'success' && !empty($request['code'])) {
                $pdo->commit();
                return $request['code'];
            }

            $attempts = (int) $request['attempts'] + 1;

            $config = require __DIR__ . '/../../config/app.php';
            $failRateKey = $provider === 'B' ? 'provider_b_fail_rate' : 'provider_a_fail_rate';
            $timeoutRateKey = $provider === 'B' ? 'provider_b_timeout_rate' : 'provider_a_timeout_rate';

            $failRate = (float) $config['supplier'][$failRateKey];
            $timeoutRate = (float) $config['supplier'][$timeoutRateKey];

            $rand = random_int(1, 1000) / 1000;

            if ($rand < $timeoutRate) {
                $stmt = $pdo->prepare('UPDATE supplier_requests SET attempts = :attempts, updated_at = NOW() WHERE request_id = :request_id');
                $stmt->execute(['attempts' => $attempts, 'request_id' => $requestId]);
                $pdo->commit();
                return null;
            }

            if ($rand < $timeoutRate + $failRate) {
                $stmt = $pdo->prepare("UPDATE supplier_requests SET status = 'error', attempts = :attempts, updated_at = NOW() WHERE request_id = :request_id");
                $stmt->execute(['attempts' => $attempts, 'request_id' => $requestId]);
                $pdo->commit();
                return null;
            }

            $code = Key::getReservedCodeForOrder($orderId, $sku);
            if (!$code) {
                $code = Key::claimAvailable($sku, $orderId);
            }

            if (!$code) {
                $stmt = $pdo->prepare("UPDATE supplier_requests SET status = 'out_of_stock', attempts = :attempts, updated_at = NOW() WHERE request_id = :request_id");
                $stmt->execute(['attempts' => $attempts, 'request_id' => $requestId]);
                Order::updateStatus($orderNumber, 'out_of_stock');
                $pdo->commit();
                return 'out_of_stock';
            }

            Key::markIssued($sku, $orderId, $code);
            $stmt = $pdo->prepare("UPDATE supplier_requests SET status = 'success', code = :code, attempts = :attempts, updated_at = NOW() WHERE request_id = :request_id");
            $stmt->execute(['code' => $code, 'attempts' => $attempts, 'request_id' => $requestId]);
            Order::updateStatus($orderNumber, 'delivered');
            $pdo->commit();
            return $code;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            try {
                Order::updateStatus($orderNumber, 'delivery_failed');
            } catch (\Throwable $ignored) {
            }
            return null;
        }
    }
}
