<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches "Source: CRM" product details from the standalone crm-test-service
 * application (see crm-test-service/ at the repo root) instead of reading
 * them straight out of the local `products` table — a genuinely separate PHP
 * process with its own SQLite database, reached only over HTTP (no shared DB
 * connection or in-process call) and authenticated with an API key (see
 * services.crm_test.api_key / CRM_TEST_SERVICE_API_KEY, which must match
 * API_KEY in crm-test-service/.env). Keyed by `source_product_id` (the
 * original local `products.id`). Shop Catalog products never go through
 * this — they keep using the existing `shop_catalog` DB connection directly.
 */
class CrmTestProductsClient
{
    /** @return Collection<int, array> keyed by source_product_id */
    public function all(): Collection
    {
        $base = rtrim(config('services.crm_test.url'), '/');

        try {
            $response = Http::timeout(3)
                ->withHeaders(['X-Api-Key' => config('services.crm_test.api_key')])
                ->get("{$base}/products");
        } catch (\Throwable $e) {
            Log::warning('crm-test-service call failed: '.$e->getMessage());

            return collect();
        }

        if (! $response->successful()) {
            return collect();
        }

        return collect($response->json())->keyBy('source_product_id');
    }
}
