<?php

declare(strict_types=1);

/**
 * crm-test-service — a standalone application, independent of the main
 * favala_CRM Laravel app: its own PHP process, its own routing, its own
 * SQLite database. It exists purely to answer GET /products with the
 * "Source: CRM" product data that favala_CRM's CrmTestProductsClient calls
 * over real HTTP, proving that call terminates in a genuinely separate
 * application rather than another connection inside the same process.
 *
 * Run with: php -S 127.0.0.1:8090 -t public   (from this folder)
 */

require __DIR__.'/../src/Env.php';
require __DIR__.'/../src/Database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

    return;
}

if ($path === '/health') {
    echo json_encode(['status' => 'ok', 'service' => 'crm-test-service']);

    return;
}

if ($path === '/products') {
    $expectedKey = crm_test_service_env('API_KEY');
    $providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

    if (! $expectedKey) {
        http_response_code(500);
        echo json_encode(['error' => 'Service misconfigured: API_KEY not set in crm-test-service/.env']);

        return;
    }

    if (! hash_equals($expectedKey, $providedKey)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);

        return;
    }

    $pdo = crm_test_service_db();
    $rows = $pdo->query(
        'SELECT source_product_id, code, legacy_code, description, category, brand, unit_of_measure FROM products ORDER BY description'
    )->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);

    return;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
