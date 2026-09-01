<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Services\OrderService;

final class AdminController extends Controller
{
    public function pending(): void
    {
        $orders = Order::pendingDeliveryOrders();
        $this->view('admin/pending', ['orders' => $orders]);
    }

    public function retry(string $orderNumber): void
    {
        try {
            OrderService::deliver($orderNumber);
            $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
