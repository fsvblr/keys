<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PaymentService;

final class WebhookController extends Controller
{
    public function payment(): void
    {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            $this->json(['error' => 'Invalid JSON'], 400);
        }

        try {
            PaymentService::process($payload);
            $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
