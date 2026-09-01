<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promocode;

final class OrderService
{
    public static function createOrder(string $sku, ?string $promoCode): array
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            $product = Product::findBySku($sku);
            if (!$product) {
                throw new \RuntimeException('Product not found');
            }

            $promocodeId = null;
            $finalAmount = (float) $product['price'];

            if ($promoCode) {
                $promo = Promocode::findAndLockAvailable($promoCode);
                if (!$promo) {
                    throw new \RuntimeException('Invalid or exhausted promocode');
                }

                $discount = PromocodeService::calculateDiscount(
                    $promo,
                    (float) $product['price'],
                    $product['currency']
                );
                $finalAmount = max(0.0, (float) $product['price'] - $discount);
                $promocodeId = (int) $promo['id'];
            }

            $orderNumber = 'ord_' . bin2hex(random_bytes(6));

            $orderId = Order::create(
                $orderNumber,
                $product['sku'],
                (float) $product['price'],
                $product['currency'],
                $promocodeId,
                $finalAmount
            );

            if ($promocodeId !== null) {
                Promocode::recordUsage($promocodeId, $orderId);
            }

            $pdo->commit();

            try {
                PaymentService::processPendingEventsByOrderRef($orderNumber);
            } catch (\Throwable $e) {
                // Pending gateway events remain unprocessed and can be retried later.
            }

            return [
                'order_number' => $orderNumber,
                'status' => 'created',
                'final_amount' => $finalAmount,
                'currency' => $product['currency'],
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function deliver(string $orderNumber): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            $order = Order::lockByOrderNumber($orderNumber);
            if (!$order) {
                $pdo->commit();
                return;
            }

            if (in_array($order['status'], ['delivered', 'payment_failed'], true)) {
                $pdo->commit();
                return;
            }

            Order::updateStatus($orderNumber, 'delivering');
            $sku = $order['product_sku'];
            $orderId = (int) $order['id'];

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return;
        }

        $requestId = $orderNumber . '-1';
        SupplierService::issue($requestId, $order['product_sku'], (int) $order['id'], $orderNumber, 'A');
    }
}
