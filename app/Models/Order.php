<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Order
{
    public static function create(string $orderNumber, string $sku, float $amount, string $currency, ?int $promocodeId, float $finalAmount): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO orders (order_number, product_sku, amount, currency, final_amount, status, promocode_id, created_at, updated_at)
             VALUES (:order_number, :product_sku, :amount, :currency, :final_amount, :status, :promocode_id, NOW(), NOW())'
        );
        $stmt->execute([
            'order_number' => $orderNumber,
            'product_sku' => $sku,
            'amount' => $amount,
            'currency' => $currency,
            'final_amount' => $finalAmount,
            'status' => 'created',
            'promocode_id' => $promocodeId,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function findByOrderNumber(string $orderNumber): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number = :order_number LIMIT 1');
        $stmt->execute(['order_number' => $orderNumber]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public static function updateStatus(string $orderNumber, string $status): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE orders SET status = :status, updated_at = NOW() WHERE order_number = :order_number');
        $stmt->execute(['status' => $status, 'order_number' => $orderNumber]);
    }

    public static function lockByOrderNumber(string $orderNumber): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number = :order_number FOR UPDATE');
        $stmt->execute(['order_number' => $orderNumber]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public static function pendingDeliveryOrders(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query("SELECT * FROM orders WHERE status IN ('out_of_stock','delivery_failed') ORDER BY updated_at DESC")->fetchAll();
    }
}
