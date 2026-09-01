<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Order;

final class PaymentService
{
    public static function process(array $payload): void
    {
        $pdo = Database::getInstance();
        $eventId = $payload['event_id'] ?? null;
        $status = $payload['status'] ?? null;
        $orderRef = $payload['order_id'] ?? null;

        if (!$eventId || !$orderRef || !$status) {
            throw new \InvalidArgumentException('event_id, order_id and status are required');
        }

        $orderRef = (string) $orderRef;

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('SELECT * FROM gateway_events WHERE event_id = :event_id FOR UPDATE');
            $stmt->execute(['event_id' => $eventId]);
            $existing = $stmt->fetch();

            if ($existing && $existing['processed_at'] !== null) {
                $pdo->commit();
                return;
            }

            if (!$existing) {
                $stmt = $pdo->prepare(
                    'INSERT INTO gateway_events (event_id, order_reference, payload, processed_at, created_at)
                     VALUES (:event_id, :order_ref, :payload, NULL, NOW())'
                );
                $stmt->execute([
                    'event_id' => $eventId,
                    'order_ref' => $orderRef,
                    'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ]);
            }

            $order = Order::findByOrderNumber($orderRef);

            if (!$order) {
                $pdo->commit();
                return;
            }

            $locked = Order::lockByOrderNumber($orderRef);
            if (!$locked) {
                $pdo->rollBack();
                throw new \RuntimeException('Order disappeared while processing');
            }

            if ($status === 'failed') {
                Order::updateStatus($orderRef, 'payment_failed');
                $pdo->prepare('UPDATE gateway_events SET processed_at = NOW() WHERE event_id = :event_id')
                    ->execute(['event_id' => $eventId]);
                $pdo->commit();
                return;
            }

            if ($status === 'paid') {
                if (in_array($locked['status'], ['created', 'paid', 'payment_failed'], true)) {
                    Order::updateStatus($orderRef, 'delivering');
                }

                $pdo->prepare('UPDATE gateway_events SET processed_at = NOW() WHERE event_id = :event_id')
                    ->execute(['event_id' => $eventId]);
                $pdo->commit();

                return;
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function processPendingEventsByOrderRef(string $orderNumber): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            'SELECT payload FROM gateway_events WHERE order_reference = :order_ref AND processed_at IS NULL'
        );
        $stmt->execute(['order_ref' => $orderNumber]);
        $events = $stmt->fetchAll();

        foreach ($events as $event) {
            $payload = json_decode($event['payload'], true);
            if (is_array($payload)) {
                self::process($payload);
            }
        }
    }
}
