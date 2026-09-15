<?php

declare(strict_types=1);

/**
 * Loads a starting per-vendor quantity for each (product, vendor) pairing
 * exported from the main app (`php artisan crm-test:export-vendor-links`) —
 * a one-off file handoff, not a runtime link. Existing rows are left alone
 * (ON CONFLICT DO NOTHING) so re-running this never resets a quantity
 * that's already moved via a real sale or return.
 */

require __DIR__.'/../src/Database.php';

$seedPath = __DIR__.'/../database/seed-vendor-links.json';

if (! file_exists($seedPath)) {
    fwrite(STDERR, "Seed file not found: {$seedPath}\n");
    fwrite(STDERR, "Run `php artisan crm-test:export-vendor-links` in the main favala_CRM app first.\n");
    exit(1);
}

$links = json_decode(file_get_contents($seedPath), true);

$pdo = crm_test_service_db();

$stmt = $pdo->prepare(<<<'SQL'
    INSERT INTO product_vendor_quantities (source_product_id, vendor_id, quantity)
    VALUES (:source_product_id, :vendor_id, :quantity)
    ON CONFLICT (source_product_id, vendor_id) DO NOTHING
    SQL);

$count = 0;
foreach ($links as $link) {
    $stmt->execute([
        'source_product_id' => $link['source_product_id'],
        'vendor_id' => $link['vendor_id'],
        'quantity' => random_int(0, 200),
    ]);
    $count += $stmt->rowCount();
}

echo "Seeded {$count} new product-vendor quantity row(s) (existing ones left untouched).\n";
