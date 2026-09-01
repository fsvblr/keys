<?php

declare(strict_types=1);

/**
 * Simple PSR-4 autoloader for the project.
 * It maps the App\ namespace to the app/ directory.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
