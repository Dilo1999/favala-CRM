<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Exports every local, non-Shop-Catalog product (Source: CRM on the Products
 * page) as a JSON seed file for the separate `crm-test-service` application
 * (see crm-test-service/) to import into its own database with its own
 * `bin/seed.php`. This is a one-off file handoff, not a runtime dependency —
 * once seeded, that service answers requests entirely from its own database,
 * and CrmTestProductsClient talks to it purely over HTTP.
 */
class ExportCrmProductsForTestService extends Command
{
    protected $signature = 'crm-test:export-products';

    protected $description = 'Export local "Source: CRM" products as a seed file for the crm-test-service app';

    public function handle(): int
    {
        $products = Product::whereNull('shop_catalog_product_id')
            ->get(['id', 'code', 'legacy_code', 'description', 'category', 'brand', 'unit_of_measure'])
            ->map(fn (Product $product) => [
                'source_product_id' => $product->id,
                'code' => $product->code,
                'legacy_code' => $product->legacy_code,
                'description' => $product->description,
                'category' => $product->category,
                'brand' => $product->brand,
                'unit_of_measure' => $product->unit_of_measure,
            ]);

        $path = base_path('crm-test-service/database/seed-products.json');

        file_put_contents($path, $products->toJson(JSON_PRETTY_PRINT));

        $this->info("Exported {$products->count()} CRM product(s) to {$path}.");
        $this->line('Run `php bin/seed.php` inside crm-test-service/ to load them into its own database.');

        return self::SUCCESS;
    }
}
