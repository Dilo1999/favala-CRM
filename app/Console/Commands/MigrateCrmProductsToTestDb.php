<?php

namespace App\Console\Commands;

use App\Models\CrmTest\Product as CrmTestProduct;
use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Copies every local, non-Shop-Catalog product (Source: CRM on the Products
 * page) into the temporary `crm_test` sandbox database, so it can be fetched
 * back into the app via an API call instead of a direct read of `products`.
 * The local `products` rows are left in place (prices, quotations, invoices,
 * etc. still reference them by id) — this only mirrors the data, it does not
 * move it.
 */
class MigrateCrmProductsToTestDb extends Command
{
    protected $signature = 'crm-test:migrate-products';

    protected $description = 'Mirror local "Source: CRM" products into the temporary crm_test database';

    public function handle(): int
    {
        $count = 0;

        Product::whereNull('shop_catalog_product_id')->chunkById(200, function ($products) use (&$count) {
            foreach ($products as $product) {
                CrmTestProduct::updateOrCreate(
                    ['source_product_id' => $product->id],
                    [
                        'code' => $product->code,
                        'legacy_code' => $product->legacy_code,
                        'description' => $product->description,
                        'category' => $product->category,
                        'brand' => $product->brand,
                        'unit_of_measure' => $product->unit_of_measure,
                    ]
                );
                $count++;
            }
        });

        $this->info("Mirrored {$count} CRM product(s) into the crm_test database.");

        return self::SUCCESS;
    }
}
