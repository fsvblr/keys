<?php

declare(strict_types=1);

require __DIR__ . '/../autoload.php';

use App\Core\Database;
use App\Core\Router;

Database::getInstance();

$config = require __DIR__ . '/../config/app.php';

$router = new Router();

$router->get('/', [App\Controllers\CatalogController::class, 'index']);
$router->get('/catalog', [App\Controllers\CatalogController::class, 'index']);
$router->get('/order/status/{orderNumber}', [App\Controllers\OrderController::class, 'status']);

$router->post('/orders', [App\Controllers\OrderController::class, 'create']);
$router->post('/simulate-payment', [App\Controllers\OrderController::class, 'simulatePayment']);
$router->post('/webhook/payment', [App\Controllers\WebhookController::class, 'payment']);

$router->get('/admin/pending', [App\Controllers\AdminController::class, 'pending']);
$router->post('/admin/retry/{orderNumber}', [App\Controllers\AdminController::class, 'retry']);

$router->post('/supplier/issue', [App\Controllers\SupplierController::class, 'issue']);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($method, $uri);
