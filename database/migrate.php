<?php

declare(strict_types=1);

require __DIR__ . '/../autoload.php';

$config = require __DIR__ . '/../config/database.php';

$driver = $config['driver'] ?? 'mysql';
$host = $config['host'];
$port = $config['port'];
$charset = $config['charset'] ?? 'utf8mb4';

$dsn = sprintf('%s:host=%s;port=%d;charset=%s', $driver, $host, $port, $charset);
$pdo = new \PDO($dsn, $config['username'], $config['password'], [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    \PDO::ATTR_EMULATE_PREPARES => false,
]);

$schemaFile = __DIR__ . '/migrations/001_schema.sql';
if (!is_file($schemaFile)) {
    echo 'Schema file not found: ' . $schemaFile . PHP_EOL;
    exit(1);
}

$schemaSql = file_get_contents($schemaFile);
foreach (array_filter(array_map('trim', explode(';', $schemaSql))) as $statement) {
    $pdo->exec($statement);
}

$seedFile = __DIR__ . '/migrations/002_seed.sql';
if (is_file($seedFile)) {
    $seedSql = file_get_contents($seedFile);
    foreach (array_filter(array_map('trim', explode(';', $seedSql))) as $statement) {
        $pdo->exec($statement);
    }
}

echo 'Migrations applied successfully.' . PHP_EOL;
