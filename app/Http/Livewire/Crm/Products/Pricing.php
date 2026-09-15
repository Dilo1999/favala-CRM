<?php

namespace App\Http\Livewire\Crm\Products;

use App\Models\Product;
use App\Models\ProductVendorPrice;
use App\Models\Vendor;
use App\Services\CrmTestProductsClient;
use App\Services\ShopCatalogSync;
use Livewire\Component;

class Pricing extends Component
{
    public Product $product;

    public array $rows = [];

    public function mount(): void
    {
        $this->product = Product::findOrFail(request()->query('productId'));

        $this->rows = $this->product->currentPrices()
            ->map(fn (ProductVendorPrice $p) => [
                'vendor_id' => $p->vendor_id,
                'price' => rtrim(rtrim(number_format((float) $p->price, 2, '.', ''), '0'), '.'),
                'existing' => true,
            ])
            ->values()
            ->all();

        if (empty($this->rows)) {
            $this->addRow();
        }
    }

    public function addRow(): void
    {
        $this->rows[] = ['vendor_id' => null, 'price' => '', 'existing' => false];
    }

    /** Deletes all price history for that vendor on this product if it was a saved row; a blank unsaved row just disappears. */
    public function removeRow(int $index): void
    {
        $row = $this->rows[$index] ?? null;

        if ($row && ($row['existing'] ?? false) && $row['vendor_id']) {
            ProductVendorPrice::where('product_id', $this->product->id)
                ->where('vendor_id', $row['vendor_id'])
                ->delete();
        }

        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    protected function rules(): array
    {
        return [
            // A row is either fully blank (an unused "Add Vendor Price" row —
            // both nullable so that passes) or fully filled in: previously
            // "price" was nullable on its own, so picking a vendor but leaving
            // price blank passed validation, save() silently skipped the row,
            // and the user saw a "Prices updated" success message for a price
            // that was never written.
            'rows.*.vendor_id' => 'nullable|exists:vendors,id|required_with:rows.*.price',
            'rows.*.price' => 'nullable|numeric|min:0|required_with:rows.*.vendor_id',
        ];
    }

    public function save(ShopCatalogSync $sync)
    {
        $this->validate();

        $currentByVendor = $this->product->currentPrices()->keyBy('vendor_id');

        foreach ($this->rows as $row) {
            if (! $row['vendor_id'] || $row['price'] === '' || $row['price'] === null) {
                continue;
            }

            $existing = $currentByVendor->get($row['vendor_id']);

            // Only write a new history entry when the vendor is new or the price actually changed —
            // the most recently entered price for a vendor/product pair is the one used elsewhere (spec §6.13).
            if ($existing && (float) $existing->price === (float) $row['price']) {
                continue;
            }

            ProductVendorPrice::create([
                'product_id' => $this->product->id,
                'vendor_id' => $row['vendor_id'],
                'price' => $row['price'],
                'added_by' => auth()->id(),
            ]);

            // If this vendor is actually one of the shops mirrored in from the
            // Shop Catalog, push the new price back there too — not just into
            // our own local vendor pricing.
            $sync->pushProductPrice($this->product, Vendor::find($row['vendor_id']), (float) $row['price']);
        }

        session()->flash('status', 'Prices updated.');

        return redirect()->route('crm.products');
    }

    public function render(CrmTestProductsClient $crmTestProducts)
    {
        // View-only here — quantity lives in crm-test-service's own database,
        // per vendor (the same product can have a different quantity with
        // each vendor carrying it), and is only ever changed by a real sale
        // or return, not edited from this page.
        $quantity = null;
        $vendorQuantities = collect();

        if (! $this->product->shop_catalog_product_id) {
            $quantity = (int) ($crmTestProducts->all()->get($this->product->id)['quantity'] ?? 0);
            $vendorQuantities = $crmTestProducts->vendorQuantities($this->product->id)
                ->map(fn ($row) => (int) $row['quantity']);
        }

        return view('crm.products.pricing', [
            'vendors' => Vendor::orderBy('company_name')->pluck('company_name', 'id'),
            'quantity' => $quantity,
            'vendorQuantities' => $vendorQuantities,
        ])->layout('layouts.crm');
    }
}
