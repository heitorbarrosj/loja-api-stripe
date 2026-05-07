<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): mysqli
{
    static $connection = null;
    static $schemaChecked = false;

    if ($connection instanceof mysqli) {
        if (!$schemaChecked) {
            ensureSchema($connection);
            $schemaChecked = true;
        }
        return $connection;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $connection = new mysqli(
        env('DB_HOST'),
        env('DB_USER'),
        env('DB_PASS', ''),
        env('DB_NAME'),
        (int) env('DB_PORT', '3306')
    );

    $connection->set_charset('utf8mb4');

    ensureSchema($connection);
    $schemaChecked = true;

    return $connection;
}

function ensureSchema(mysqli $connection): void
{
    $result = $connection->query("SHOW COLUMNS FROM users LIKE 'address'");

    if ($result instanceof mysqli_result && $result->num_rows === 0) {
        $connection->query('ALTER TABLE users ADD COLUMN address TEXT NULL AFTER password_hash');
    }

    if ($result instanceof mysqli_result) {
        $result->close();
    }

    $result = $connection->query("SHOW COLUMNS FROM orders LIKE 'shipping_address'");

    if ($result instanceof mysqli_result && $result->num_rows === 0) {
        $connection->query('ALTER TABLE orders ADD COLUMN shipping_address TEXT NULL AFTER amount');
    }

    if ($result instanceof mysqli_result) {
        $result->close();
    }
}
