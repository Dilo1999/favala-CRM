<?php

declare(strict_types=1);

require_once __DIR__.'/Env.php';

/**
 * This service's own PostgreSQL database (`favala_crm_test`) — a separate
 * database inside the same Postgres server the main favala_CRM app uses for
 * `favala_crm` / `favala_shop_catalog`, matching that architecture, but
 * reached only through this service's own connection (see .env below) —
 * never through the main app's Eloquent connections or code.
 */
function crm_test_service_db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host = crm_test_service_env('DB_HOST') ?: '127.0.0.1';
    $port = crm_test_service_env('DB_PORT') ?: '5432';
    $database = crm_test_service_env('DB_DATABASE') ?: 'favala_crm_test';
    $username = crm_test_service_env('DB_USERNAME') ?: 'postgres';
    $password = crm_test_service_env('DB_PASSWORD') ?: '';

    $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec(<<<'SQL'
        CREATE TABLE IF NOT EXISTS products (
            id SERIAL PRIMARY KEY,
            source_product_id INTEGER UNIQUE NOT NULL,
            code TEXT UNIQUE NOT NULL,
            legacy_code TEXT NULL,
            description TEXT NOT NULL,
            category TEXT NULL,
            brand TEXT NULL,
            unit_of_measure TEXT NULL,
            quantity INTEGER NOT NULL DEFAULT 0
        )
        SQL);

    return $pdo;
}
