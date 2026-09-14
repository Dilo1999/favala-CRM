<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches "Source: CRM" product details from the temporary crm_test API
 * (see CrmTestProductsController) instead of reading them straight out of
 * the local `products` table — a prototype of moving that data behind a
 * separate service, keyed by `source_product_id` (the original local
 * `products.id`). Shop Catalog products never go through this — they keep
 * using the existing `shop_catalog` DB connection directly.
 */
class CrmTestProductsClient
{
    /** @return Collection<int, array> keyed by source_product_id */
    public function all(): Collection
    {
        try {
            // Built from the current request's own scheme+host (not the APP_URL
            // config) since this is a loopback call to the same app instance
            // that's serving the page — that stays correct no matter which
            // host/port the dev server is actually reachable on.
            $base = request()->getSchemeAndHttpHost();
            $response = Http::timeout(3)->get($base.'/api/crm-test/products');
        } catch (\Throwable $e) {
            Log::warning('crm_test products API call failed: '.$e->getMessage());

            return collect();
        }

        if (! $response->successful()) {
            return collect();
        }

        return collect($response->json())->keyBy('source_product_id');
    }
}
