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
     * Per-vendor quantity breakdown for one product — the same product can
     * have a different quantity with each vendor carrying it, so there's no
     * single "the" quantity outside of their sum (which `all()` above reports).
     *
     * @return Collection<int, array> keyed by vendor_id
     */
    public function vendorQuantities(int $sourceProductId): Collection
    {
        $base = rtrim(config('services.crm_test.url'), '/');

        try {
            $response = Http::timeout(3)
                ->withHeaders(['X-Api-Key' => config('services.crm_test.api_key')])
                ->get("{$base}/products/{$sourceProductId}/vendor-quantities");
        } catch (\Throwable $e) {
            Log::warning('crm-test-service vendor-quantities call failed: '.$e->getMessage());

            return collect();
        }

        if (! $response->successful()) {
            return collect();
        }

        return collect($response->json())->keyBy('vendor_id');
    }

    /**
     * Per-vendor quantity for several products at once, in a single request —
     * used by the product search picker so showing quantity next to each
     * result doesn't cost one HTTP round trip per matched product.
     *
     * @param  int[]  $sourceProductIds
     * @return Collection<int, Collection<int, int>> quantity keyed by [source_product_id][vendor_id]
     */
    public function vendorQuantitiesForProducts(array $sourceProductIds): Collection
    {
        $sourceProductIds = array_values(array_unique(array_filter($sourceProductIds)));

        if (empty($sourceProductIds)) {
            return collect();
        }

        $base = rtrim(config('services.crm_test.url'), '/');

        try {
            $response = Http::timeout(3)
                ->withHeaders(['X-Api-Key' => config('services.crm_test.api_key')])
                ->get("{$base}/vendor-quantities", ['ids' => implode(',', $sourceProductIds)]);
        } catch (\Throwable $e) {
            Log::warning('crm-test-service bulk vendor-quantities call failed: '.$e->getMessage());

            return collect();
        }

        if (! $response->successful()) {
            return collect();
        }

        return collect($response->json())
            ->groupBy('source_product_id')
            ->map(fn (Collection $rows) => $rows->keyBy('vendor_id')->map(fn ($row) => (int) $row['quantity']));
    }

    /**
     * Atomically increment (positive) or decrement (negative) one vendor's
     * quantity for one product — used for stock movements (invoice sale /
     * refunded return), scoped to whichever vendor that line was priced at.
     */
    public function adjustVendorQuantity(int $sourceProductId, int $vendorId, int $delta): bool
    {
        $base = rtrim(config('services.crm_test.url'), '/');

        try {
            $response = Http::timeout(3)
                ->withHeaders(['X-Api-Key' => config('services.crm_test.api_key')])
                ->post("{$base}/products/{$sourceProductId}/vendor-quantities/{$vendorId}/adjust", ['delta' => $delta]);
        } catch (\Throwable $e) {
            Log::warning('crm-test-service vendor quantity adjust failed: '.$e->getMessage());

            return false;
        }

        return $response->successful();
    }
}
