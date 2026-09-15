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

if ($path === '/health' && $method === 'GET') {
    echo json_encode(['status' => 'ok', 'service' => 'crm-test-service']);

    return;
}

/** Every other route needs the API key, checked once here. */
function require_valid_api_key(): bool
{
    $expectedKey = crm_test_service_env('API_KEY');
    $providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

    if (! $expectedKey) {
        http_response_code(500);
        echo json_encode(['error' => 'Service misconfigured: API_KEY not set in crm-test-service/.env']);

        return false;
    }

    if (! hash_equals($expectedKey, $providedKey)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);

        return false;
    }

    return true;
}

if ($path === '/products' && $method === 'GET') {
    if (! require_valid_api_key()) {
        return;
    }

    // Optional ?search= filters by product name (description), code, or
    // brand — case-insensitive, matching how the main app's own product
    // searches work.
    $search = trim((string) ($_GET['search'] ?? ''));

    $sql = 'SELECT source_product_id, code, legacy_code, description, category, brand, unit_of_measure, quantity FROM products';
    $params = [];

    if ($search !== '') {
        $sql .= ' WHERE description ILIKE :term OR code ILIKE :term OR brand ILIKE :term';
        $params['term'] = '%'.$search.'%';
    }

    $sql .= ' ORDER BY description';

    $pdo = crm_test_service_db();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    return;
}

// Quantity lives only in this service's own database — this is the one
// place it can be changed, so favala_CRM's Products page calls out to here
// instead of holding its own copy.
if (preg_match('#^/products/(\d+)/quantity$#', $path, $m) && $method === 'POST') {
    if (! require_valid_api_key()) {
        return;
    }

    $sourceProductId = (int) $m[1];
    $body = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];
    $quantity = $body['quantity'] ?? null;

    if (! is_numeric($quantity) || (int) $quantity < 0 || (float) $quantity != (int) $quantity) {
        http_response_code(422);
        echo json_encode(['error' => 'quantity must be a non-negative integer']);

        return;
    }

    $pdo = crm_test_service_db();
    $stmt = $pdo->prepare('UPDATE products SET quantity = :quantity WHERE source_product_id = :id');
    $stmt->execute(['quantity' => (int) $quantity, 'id' => $sourceProductId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);

        return;
    }

    echo json_encode(['source_product_id' => $sourceProductId, 'quantity' => (int) $quantity]);

    return;
}

// Atomic increment/decrement — used by favala_CRM's invoice creation (stock
// leaves on sale) and refunded returns (stock comes back), instead of the
// read-then-set /quantity endpoint above, to avoid a lost-update race when
// two requests adjust the same product's quantity at once. Clamped at 0
// rather than rejected, since this app has no stock-availability check
// before a sale is made.
if (preg_match('#^/products/(\d+)/quantity/adjust$#', $path, $m) && $method === 'POST') {
    if (! require_valid_api_key()) {
        return;
    }

    $sourceProductId = (int) $m[1];
    $body = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];
    $delta = $body['delta'] ?? null;

    if (! is_numeric($delta) || (float) $delta != (int) $delta) {
        http_response_code(422);
        echo json_encode(['error' => 'delta must be an integer']);

        return;
    }

    $pdo = crm_test_service_db();
    $stmt = $pdo->prepare(
        'UPDATE products SET quantity = GREATEST(quantity + :delta, 0) WHERE source_product_id = :id RETURNING quantity'
    );
    $stmt->execute(['delta' => (int) $delta, 'id' => $sourceProductId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $row) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);

        return;
    }

    echo json_encode(['source_product_id' => $sourceProductId, 'quantity' => (int) $row['quantity']]);

    return;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
