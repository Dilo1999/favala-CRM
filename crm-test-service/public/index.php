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

    // quantity here is the sum across every vendor carrying this product
    // (see product_vendor_quantities) — a product carried by several vendors
    // can have a different quantity with each one; GET /products/{id}/vendor-quantities
    // returns that breakdown.
    $sql = <<<'SQL'
        SELECT p.source_product_id, p.code, p.legacy_code, p.description, p.category, p.brand, p.unit_of_measure,
               COALESCE(SUM(v.quantity), 0) AS quantity
        FROM products p
        LEFT JOIN product_vendor_quantities v ON v.source_product_id = p.source_product_id
        SQL;
    $params = [];

    if ($search !== '') {
        $sql .= ' WHERE p.description ILIKE :term OR p.code ILIKE :term OR p.brand ILIKE :term';
        $params['term'] = '%'.$search.'%';
    }

    $sql .= ' GROUP BY p.source_product_id, p.code, p.legacy_code, p.description, p.category, p.brand, p.unit_of_measure
               ORDER BY p.description';

    $pdo = crm_test_service_db();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    return;
}

// Per-vendor quantity breakdown for one product — e.g. the Update Pricing
// page shows this next to each vendor's price, since the same product can
// have a different quantity with each vendor carrying it.
if (preg_match('#^/products/(\d+)/vendor-quantities$#', $path, $m) && $method === 'GET') {
    if (! require_valid_api_key()) {
        return;
    }

    $sourceProductId = (int) $m[1];
    $pdo = crm_test_service_db();
    $stmt = $pdo->prepare('SELECT vendor_id, quantity FROM product_vendor_quantities WHERE source_product_id = :id ORDER BY vendor_id');
    $stmt->execute(['id' => $sourceProductId]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    return;
}

// Bulk per-vendor quantity lookup across several products at once — used by
// the product search picker (Deals/Quotations/Invoices) so showing quantity
// next to each search result doesn't need one request per matched product.
if ($path === '/vendor-quantities' && $method === 'GET') {
    if (! require_valid_api_key()) {
        return;
    }

    $ids = array_values(array_unique(array_filter(array_map(
        'intval',
        explode(',', (string) ($_GET['ids'] ?? ''))
    ))));

    if (empty($ids)) {
        echo json_encode([]);

        return;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $pdo = crm_test_service_db();
    $stmt = $pdo->prepare("SELECT source_product_id, vendor_id, quantity FROM product_vendor_quantities WHERE source_product_id IN ({$placeholders})");
    $stmt->execute($ids);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    return;
}

// Atomic increment/decrement of one vendor's quantity for one product — used
// by favala_CRM's invoice creation (stock leaves on sale, for whichever
// vendor that line was priced at) and refunded returns (stock comes back to
// that same vendor). Upserts so the first sale/return for a product+vendor
// pair doesn't need a row to already exist. Clamped at 0 rather than
// rejected, since this app has no stock-availability check before a sale.
if (preg_match('#^/products/(\d+)/vendor-quantities/(\d+)/adjust$#', $path, $m) && $method === 'POST') {
    if (! require_valid_api_key()) {
        return;
    }

    $sourceProductId = (int) $m[1];
    $vendorId = (int) $m[2];
    $body = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];
    $delta = $body['delta'] ?? null;

    if (! is_numeric($delta) || (float) $delta != (int) $delta) {
        http_response_code(422);
        echo json_encode(['error' => 'delta must be an integer']);

        return;
    }

    $pdo = crm_test_service_db();
    $stmt = $pdo->prepare(<<<'SQL'
        INSERT INTO product_vendor_quantities (source_product_id, vendor_id, quantity)
        VALUES (:pid, :vid, GREATEST(:delta, 0))
        ON CONFLICT (source_product_id, vendor_id) DO UPDATE
        SET quantity = GREATEST(product_vendor_quantities.quantity + :delta, 0)
        RETURNING quantity
        SQL);
    $stmt->execute(['pid' => $sourceProductId, 'vid' => $vendorId, 'delta' => (int) $delta]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['source_product_id' => $sourceProductId, 'vendor_id' => $vendorId, 'quantity' => (int) $row['quantity']]);

    return;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
