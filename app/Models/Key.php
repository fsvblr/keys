<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Key
{
    public static function claimAvailable(string $sku, int $orderId): ?string
    {
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare(
            'SELECT id, code FROM `keys`
             WHERE sku = :sku AND order_id IS NULL AND status = :status
             ORDER BY id
             LIMIT 1
             FOR UPDATE'
        );
        $stmt->execute(['sku' => $sku, 'status' => 'available']);
        $key = $stmt->fetch();

        if (!$key) {
            return null;
        }

        $stmt = $pdo->prepare(
            "UPDATE `keys`
             SET order_id = :order_id, status = 'reserved', reserved_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute(['order_id' => $orderId, 'id' => $key['id']]);

        return $key['code'];
    }

    public static function markIssued(string $sku, int $orderId, string $code): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "UPDATE `keys`
             SET status = 'issued', issued_at = NOW()
             WHERE sku = :sku AND order_id = :order_id AND code = :code"
        );
        $stmt->execute(['sku' => $sku, 'order_id' => $orderId, 'code' => $code]);
    }

    public static function getReservedCodeForOrder(int $orderId, string $sku): ?string
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT code FROM `keys` 
             WHERE order_id = :order_id AND sku = :sku AND status = 'reserved'
             LIMIT 1"
        );
        $stmt->execute(['order_id' => $orderId, 'sku' => $sku]);
        $row = $stmt->fetch();
        return $row ? $row['code'] : null;
    }

    public static function getIssuedCodeForOrder(int $orderId): ?string
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT code FROM `keys`
             WHERE order_id = :order_id AND status = 'issued'
             LIMIT 1"
        );
        $stmt->execute(['order_id' => $orderId]);
        $row = $stmt->fetch();
        return $row ? $row['code'] : null;
    }
}
