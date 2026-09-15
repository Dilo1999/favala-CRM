<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches "Source: CRM" product details from the standalone crm-test-service
 * application (see crm-test-service/ at the repo root) instead of reading
 * them straight out of the local `products` table — a genuinely separate PHP
 * process with its own PostgreSQL database, reached only over HTTP (no
 * shared DB connection or in-process call) and authenticated with an API key (see
 * services.crm_test.api_key / CRM_TEST_SERVICE_API_KEY, which must match
 * API_KEY in crm-test-service/.env). Keyed by `source_product_id` (the
 * original local `products.id`). Shop Catalog products never go through
 * this — they keep using the existing `shop_catalog` DB connection directly.
 */
class CrmTestProductsClient
{
    /**
     * @param  string|null  $search  Filters by product name (description), code, or brand — matches
     *                               how the main app's own product searches work. Omit for everything.
     * @return Collection<int, array> keyed by source_product_id
     */
    public function all(?string $search = null): Collection
    {
        $base = rtrim(config('services.crm_test.url'), '/');

        try {
            $response = Http::timeout(3)
                ->withHeaders(['X-Api-Key' => config('services.crm_test.api_key')])
                ->get("{$base}/products", array_filter(['search' => $search]));
        } catch (\Throwable $e) {
            Log::warning('crm-test-service call failed: '.$e->getMessage());

            return collect();
        }

        if (! $response->successful()) {
            return collect();
        }

        return collect($response->json())->keyBy('source_product_id');
    }

    /**
     * Quantity lives only in crm-test-service's own database, so this is the
     * one place it can be changed — the Products page has no local copy to
     * update instead.
     */
    public function updateQuantity(int $sourceProductId, int $quantity): bool
    {
        $base = rtrim(config('services.crm_test.url'), '/');

        try {
            $response = Http::timeout(3)
                ->withHeaders(['X-Api-Key' => config('services.crm_test.api_key')])
                ->post("{$base}/products/{$sourceProductId}/quantity", ['quantity' => $quantity]);
        } catch (\Throwable $e) {
            Log::warning('crm-test-service quantity update failed: '.$e->getMessage());

            return false;
        }

        return $response->successful();
    }

    /**
     * Atomically increment (positive) or decrement (negative) a product's
     * quantity — used for stock movements (invoice sale / refunded return)
     * where a lost-update race against a concurrent adjustment matters,
     * unlike updateQuantity() above which just sets an absolute value from
     * the Products page's edit modal.
     */
    public function adjustQuantity(int $sourceProductId, int $delta): bool
    {
        $base = rtrim(config('services.crm_test.url'), '/');

        try {
            $response = Http::timeout(3)
                ->withHeaders(['X-Api-Key' => config('services.crm_test.api_key')])
                ->post("{$base}/products/{$sourceProductId}/quantity/adjust", ['delta' => $delta]);
        } catch (\Throwable $e) {
            Log::warning('crm-test-service quantity adjust failed: '.$e->getMessage());

            return false;
        }

        return $response->successful();
    }
}
