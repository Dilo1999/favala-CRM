<?php

namespace App\Services;

use App\Models\Product;

/**
 * Applies stock quantity changes to "Source: CRM" products (those with no
 * shop_catalog_product_id — see Product::getSourceLabelAttribute()) via the
 * crm-test-service API. Shop Catalog products are never touched here; any
 * stock they carry lives in that separate system instead.
 *
 * Invoice creation is the one point in this app that represents a confirmed
 * sale, so it's the only place stock decrements — not Deals or Quotations
 * (pre-sale documents) and not Deliveries (fulfillment of an already-sold
 * line, not a second sale). A refunded return is the mirror event: it
 * restores stock, and un-refunding a return reverses that restoration.
 */
class ProductStockService
{
    public function __construct(private CrmTestProductsClient $client)
    {
    }

    /** @param iterable<array{product_id:int,qty:mixed}|object{product_id:int,qty:mixed}> $lines */
    public function decrement(iterable $lines): void
    {
        $this->apply($lines, -1);
    }

    /** @param iterable<array{product_id:int,qty:mixed}|object{product_id:int,qty:mixed}> $lines */
    public function restore(iterable $lines): void
    {
        $this->apply($lines, 1);
    }

    private function apply(iterable $lines, int $sign): void
    {
        foreach ($lines as $line) {
            $productId = is_array($line) ? $line['product_id'] : $line->product_id;
            $qty = is_array($line) ? $line['qty'] : $line->qty;

            $product = Product::find($productId);

            if (! $product || $product->shop_catalog_product_id) {
                continue; // Shop Catalog products don't carry a quantity here.
            }

            $this->client->adjustQuantity($product->id, $sign * (int) round((float) $qty));
        }
    }
}
