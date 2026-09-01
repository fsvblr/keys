<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function view(string $template, array $data = []): void
    {
        $viewPath = __DIR__ . '/../Views/';
        $layout = $viewPath . 'layouts/main.php';

        $content = View::render($template, $data);

        include $layout;
    }
}
