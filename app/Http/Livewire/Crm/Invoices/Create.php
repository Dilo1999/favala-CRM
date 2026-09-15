<?php

namespace App\Http\Livewire\Crm\Invoices;

use App\Http\Livewire\Concerns\HasProductSearch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVendorPrice;
use App\Services\PricingEngine;
use App\Services\ProductStockService;
use Livewire\Component;

class Create extends Component
{
    use HasProductSearch;

    // Deliberately untyped: bound live via wire:model to a <select> whose blank
    // "Select customer…" option submits "", and PHP's typed-property coercion
    // rejects "" => ?int with an uncaught TypeError before validation ever runs.
    public $customer_id = null;

    public string $invoice_date;

    public string $expiry_date;

    public ?string $bill_to_name = null;

    public ?string $bill_to_phone = null;

    public ?string $bill_to_address = null;

    public array $items = [];

    public string $discount_type = 'flat';

    public float $discount_value = 0;

    public float $gst_percent = 8;

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
        $this->expiry_date = now()->addDays(config('crm.document_expiry_days'))->toDateString();
        $this->gst_percent = (float) config('crm.gst_percent');
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null, 'product_label' => null, 'vendor_id' => null, 'cost' => 0, 'qty' => 1,
            'markup_percent' => (float) config('crm.default_markup_percent'), 'discount_type' => 'flat', 'discount_value' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /**
     * $vendorId/$cost let a caller apply a *specific* vendor's price (e.g. the
     * one actually picked from the product search list) instead of always
     * defaulting to the cheapest — passing neither keeps the old "cheapest
     * wins" behavior for callers that don't care which vendor.
     */
    public function updateItemProduct(int $index, ?string $productId, ?int $vendorId = null, ?float $cost = null): void
    {
        $product = $productId ? Product::find($productId) : null;

        if ($vendorId === null) {
            $best = $product?->cheapestCurrentPrice();
            $vendorId = $best?->vendor_id;
            $cost = (float) ($best?->price ?? 0);
        }

        $this->items[$index]['product_id'] = $productId;
        $this->items[$index]['product_label'] = $product?->description;
        $this->items[$index]['vendor_id'] = $vendorId;
        $this->items[$index]['cost'] = (float) ($cost ?? 0);
    }

    /** Called by the <x-product-search> picker. */
    public function pickProduct(int $index, string $key): void
    {
        $selection = $this->resolveProductSelection($key);
        $this->updateItemProduct($index, (string) $selection->product_id, $selection->vendor_id, $selection->price);
        $this->closeProductSearch();
    }

    public function getLinesProperty(): array
    {
        return collect($this->items)->map(fn ($item) => PricingEngine::line(
            (float) $item['cost'], (float) $item['qty'], (float) $item['markup_percent'],
            $item['discount_type'], (float) $item['discount_value']
        ))->all();
    }

    public function getSummaryProperty(): array
    {
        return PricingEngine::order($this->lines, $this->discount_type, $this->discount_value, $this->gst_percent);
    }

    protected function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
        ];
    }

    public function save(ProductStockService $stock)
    {
        $this->validate();

        $summary = $this->summary;
        $lines = $this->lines;

        $invoice = Invoice::create([
            'customer_id' => (int) $this->customer_id,
            'staff_id' => auth()->id(),
            'invoice_date' => $this->invoice_date,
            'expiry_date' => $this->expiry_date,
            'bill_to_name' => $this->bill_to_name,
            'bill_to_phone' => $this->bill_to_phone,
            'bill_to_address' => $this->bill_to_address,
            'subtotal' => $summary['subtotal'],
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'gst_percent' => $this->gst_percent,
            'gst_amount' => $summary['gst_amount'],
            'grand_total' => $summary['grand_total'],
            'amount_paid' => 0,
            'balance_due' => $summary['grand_total'],
            'payment_status' => 'pending',
        ]);

        foreach ($this->items as $i => $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'rate' => $lines[$i]['unit_price'],
                'discount_type' => $item['discount_type'],
                'discount_value' => $item['discount_value'],
                'amount' => $lines[$i]['line_amount'],
                'sort_order' => $i,
            ]);
        }

        // A confirmed sale — the one point that actually consumes stock.
        $stock->decrement($this->items);

        session()->flash('status', 'Invoice created.');

        return redirect()->route('crm.invoices.show', $invoice);
    }

    public function render()
    {
        return view('crm.invoices.create', [
            'customers' => Customer::orderBy('company_name')->limit(300)->pluck('company_name', 'id'),
        ])->layout('layouts.crm');
    }
}
