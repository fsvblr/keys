<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Product
{
    public static function all(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query('SELECT * FROM products ORDER BY id')->fetchAll();
    }

    public static function findBySku(string $sku): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM products WHERE sku = :sku LIMIT 1');
        $stmt->execute(['sku' => $sku]);
        $product = $stmt->fetch();
        return $product ?: null;
    }
}
