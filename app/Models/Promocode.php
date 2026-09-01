<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Promocode
{
    public static function findAndLockAvailable(string $code): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM promocodes
             WHERE code = :code
             FOR UPDATE'
        );
        $stmt->execute(['code' => $code]);
        $promo = $stmt->fetch();

        if (!$promo) {
            return null;
        }

        $stmt = $pdo->prepare(
            'UPDATE promocodes
             SET used_count = used_count + 1
             WHERE code = :code AND used_count < max_uses'
        );
        $stmt->execute(['code' => $code]);

        return $stmt->rowCount() > 0 ? $promo : null;
    }

    public static function recordUsage(int $promocodeId, int $orderId): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO promocode_usages (promocode_id, order_id, created_at)
             VALUES (:promocode_id, :order_id, NOW())
             ON DUPLICATE KEY UPDATE created_at = created_at'
        );
        $stmt->execute(['promocode_id' => $promocodeId, 'order_id' => $orderId]);
    }
}
