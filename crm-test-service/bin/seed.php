<?php

declare(strict_types=1);

/**
 * Loads crm-test-service's own database from database/seed-products.json —
 * a one-off file handoff from the main app (generate it there with
 * `php artisan crm-test:export-products`). Once seeded, this service answers
 * every request purely from its own database; nothing here reaches back into
 * the main app or its databases at request time.
 */

require __DIR__.'/../src/Database.php';

$seedPath = __DIR__.'/../database/seed-products.json';

if (! file_exists($seedPath)) {
    fwrite(STDERR, "Seed file not found: {$seedPath}\n");
    fwrite(STDERR, "Run `php artisan crm-test:export-products` in the main favala_CRM app first.\n");
    exit(1);
}

$products = json_decode(file_get_contents($seedPath), true);

$pdo = crm_test_service_db();

$stmt = $pdo->prepare(<<<'SQL'
    INSERT INTO products (source_product_id, code, legacy_code, description, category, brand, unit_of_measure)
    VALUES (:source_product_id, :code, :legacy_code, :description, :category, :brand, :unit_of_measure)
    ON CONFLICT(source_product_id) DO UPDATE SET
        code = excluded.code,
        legacy_code = excluded.legacy_code,
        description = excluded.description,
        category = excluded.category,
        brand = excluded.brand,
        unit_of_measure = excluded.unit_of_measure
    SQL);

foreach ($products as $product) {
    $stmt->execute([
        'source_product_id' => $product['source_product_id'],
        'code' => $product['code'],
        'legacy_code' => $product['legacy_code'],
        'description' => $product['description'],
        'category' => $product['category'],
        'brand' => $product['brand'],
        'unit_of_measure' => $product['unit_of_measure'],
    ]);
}

echo 'Seeded '.count($products)." product(s) into crm-test-service's own database.\n";
