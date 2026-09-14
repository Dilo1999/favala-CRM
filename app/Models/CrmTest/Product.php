<?php

namespace App\Models\CrmTest;

use Illuminate\Database\Eloquent\Model;

/**
 * Mirrors "Source: CRM" rows out of the main `products` table into the
 * separate `crm_test` sandbox database — populated by the
 * `crm-test:migrate-products` command and served back to the app via
 * CrmTestProductsController, so CrmTestProductsClient can fetch them
 * through an API call instead of reading `products` directly.
 */
class Product extends Model
{
    protected $connection = 'crm_test';

    protected $table = 'crm_products';

    protected $fillable = [
        'source_product_id', 'code', 'legacy_code', 'description', 'category', 'brand', 'unit_of_measure',
    ];
}
