<?php

declare(strict_types=1);

/**
 * This service's own SQLite database — physically separate from the main
 * favala_CRM Laravel app's databases (favala_crm / favala_shop_catalog).
 * Nothing here shares a DB connection, a PHP process, or a codebase with
 * that app; the two only ever exchange data over HTTP (see public/index.php)
 * or, at seed time, the JSON file bin/seed.php reads.
 */
function crm_test_service_db(): PDO
{
    $path = __DIR__.'/../database/products.sqlite';

    $isNew = ! file_exists($path);

    $pdo = new PDO('sqlite:'.$path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($isNew) {
        $pdo->exec(<<<'SQL'
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                source_product_id INTEGER UNIQUE NOT NULL,
                code TEXT UNIQUE NOT NULL,
                legacy_code TEXT NULL,
                description TEXT NOT NULL,
                category TEXT NULL,
                brand TEXT NULL,
                unit_of_measure TEXT NULL
            )
            SQL);
    }

    return $pdo;
}
