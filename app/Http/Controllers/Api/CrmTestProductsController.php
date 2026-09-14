<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CrmTest\Product;

/**
 * Serves the "Source: CRM" products mirrored into the temporary `crm_test`
 * database (see MigrateCrmProductsToTestDb), so CrmTestProductsClient can
 * fetch them via HTTP instead of reading the local `products` table directly.
 */
class CrmTestProductsController extends Controller
{
    public function index()
    {
        return Product::query()
            ->get(['source_product_id', 'code', 'legacy_code', 'description', 'category', 'brand', 'unit_of_measure']);
    }
}
