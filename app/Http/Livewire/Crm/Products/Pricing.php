<?php

namespace App\Http\Livewire\Crm\Products;

use App\Models\Product;
use App\Models\ProductVendorPrice;
use App\Models\Vendor;
use Livewire\Component;

class Pricing extends Component
{
    public Product $product;

    public array $rows = [];

    public ?int $historyVendorId = null;

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

        if ($this->historyVendorId === ($row['vendor_id'] ?? null)) {
            $this->historyVendorId = null;
        }
    }

    public function toggleHistory(?int $vendorId): void
    {
        $this->historyVendorId = $this->historyVendorId === $vendorId ? null : $vendorId;
    }

    public function getHistoryProperty()
    {
        if (! $this->historyVendorId) {
            return collect();
        }

        return $this->product->prices()
            ->where('vendor_id', $this->historyVendorId)
            ->with('addedBy')
            ->latest('id')
            ->get();
    }

    protected function rules(): array
    {
        return [
            'rows.*.vendor_id' => 'nullable|exists:vendors,id',
            'rows.*.price' => 'nullable|numeric|min:0',
        ];
    }

    public function save()
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
        }

        session()->flash('status', 'Prices updated.');

        return redirect()->route('crm.products');
    }

    public function render()
    {
        return view('crm.products.pricing', [
            'vendors' => Vendor::orderBy('company_name')->pluck('company_name', 'id'),
        ])->layout('layouts.crm');
    }
}
