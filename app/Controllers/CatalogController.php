<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

final class CatalogController extends Controller
{
    public function index(): void
    {
        $products = Product::all();
        $this->view('catalog/index', [
            'products' => $products,
        ]);
    }
}
