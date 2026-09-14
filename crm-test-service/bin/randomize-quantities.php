<?php

declare(strict_types=1);

/**
 * Assigns a random quantity to every product in this service's own database —
 * for testing/demo purposes, so the Products page doesn't show 0 for
 * everything. Run from this folder: php bin/randomize-quantities.php
 */

require __DIR__.'/../src/Database.php';

$pdo = crm_test_service_db();
$ids = $pdo->query('SELECT source_product_id FROM products')->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare('UPDATE products SET quantity = :quantity WHERE source_product_id = :id');

foreach ($ids as $id) {
    $stmt->execute(['quantity' => random_int(0, 500), 'id' => $id]);
}

echo 'Randomized quantity for '.count($ids)." product(s).\n";
