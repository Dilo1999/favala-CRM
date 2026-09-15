<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Exports every (product, vendor) pairing for local, non-Shop-Catalog
 * products (Source: CRM) as a JSON seed file, so the separate
 * crm-test-service application (see crm-test-service/) can seed a starting
 * per-vendor quantity for each pairing with its own bin/seed-vendor-quantities.php.
 */
class ExportProductVendorLinksForTestService extends Command
{
    protected $signature = 'crm-test:export-vendor-links';

    protected $description = 'Export (product, vendor) pairs for Source: CRM products, for crm-test-service to seed per-vendor quantities';

    public function handle(): int
    {
        $links = Product::whereNull('shop_catalog_product_id')
            ->with('prices.vendor')
            ->get()
            ->flatMap(fn (Product $product) => $product->currentPrices()
                ->map(fn ($price) => [
                    'source_product_id' => $product->id,
                    'vendor_id' => $price->vendor_id,
                ]))
            ->values();

        $path = base_path('crm-test-service/database/seed-vendor-links.json');

        file_put_contents($path, $links->toJson(JSON_PRETTY_PRINT));

        $this->info("Exported {$links->count()} product-vendor link(s) to {$path}.");
        $this->line('Run `php bin/seed-vendor-quantities.php` inside crm-test-service/ to load them.');

        return self::SUCCESS;
    }
}
