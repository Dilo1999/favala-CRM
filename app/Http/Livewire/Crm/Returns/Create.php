<?php

namespace App\Http\Livewire\Crm\Returns;

use App\Models\Invoice;
use App\Models\SalesReturn;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public Invoice $invoice;

    public string $date;

    public ?string $reason = null;

    public array $lines = [];

    public function mount(): void
    {
        $this->invoice = Invoice::with('items.product')->findOrFail(request()->query('invoiceId'));
        $this->date = now()->toDateString();

        $this->lines = $this->invoice->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'label' => $item->product?->description,
            'max_qty' => $item->qty,
            'rate' => $item->rate,
            'qty' => 0,
        ])->all();
    }

    protected function rules(): array
    {
        return [
            'date' => 'required|date',
            'reason' => 'nullable|string',
            // The <input>'s HTML max="{{ max_qty }}" is client-side only and
            // trivially bypassed (paste, autofill, a raw request) — capping it
            // here too is what actually stops a return being logged for more
            // than was invoiced, which would otherwise corrupt the refund math
            // in SalesReturn::applyRefundToInvoice().
            'lines.*.qty' => Rule::forEach(function ($value, $attribute) {
                preg_match('/^lines\.(\d+)\.qty$/', $attribute, $m);
                $max = (float) ($this->lines[(int) $m[1]]['max_qty'] ?? 0);

                return ['required', 'numeric', 'min:0', 'max:'.$max];
            }),
        ];
    }

    /**
     * Pre-GST, post-line-discount amount for this line's return qty —
     * mirrors how InvoiceItem::amount is computed (before GST), rather than
     * qty × rate, which ignores whatever discount the original invoice line
     * carried.
     */
    protected function lineReturnAmount(int $index): float
    {
        $item = $this->invoice->items->values()->get($index);
        $qty = (float) ($this->lines[$index]['qty'] ?? 0);

        if (! $item || (float) $item->qty <= 0 || $qty <= 0) {
            return 0.0;
        }

        return round(((float) $item->amount / (float) $item->qty) * $qty, 2);
    }

    public function getTotalValueProperty(): float
    {
        $returnedSubtotal = collect($this->lines)->keys()->sum(fn ($i) => $this->lineReturnAmount($i));
        $subtotal = (float) $this->invoice->subtotal;

        if ($subtotal <= 0 || $returnedSubtotal <= 0) {
            return 0.0;
        }

        // Scales the pre-GST returned subtotal by the same ratio the whole
        // invoice's grand total bears to its subtotal — folds in both GST and
        // any order-level discount proportionally, so returning every line in
        // full refunds exactly the invoice's grand_total, not just its subtotal.
        return round($returnedSubtotal * ((float) $this->invoice->grand_total / $subtotal), 2);
    }

    public function save()
    {
        $this->validate();

        $itemsToReturn = collect($this->lines)->filter(fn ($l) => (float) $l['qty'] > 0);

        if ($itemsToReturn->isEmpty()) {
            $this->addError('lines', 'Select at least one item to return.');

            return;
        }

        $return = SalesReturn::create([
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->invoice->customer_id,
            'date' => $this->date,
            'status' => SalesReturn::STATUS_PENDING,
            'value' => $this->totalValue,
            'reason' => $this->reason,
            'created_by' => auth()->id(),
        ]);

        foreach ($itemsToReturn as $i => $line) {
            $return->items()->create([
                'product_id' => $line['product_id'],
                'qty' => $line['qty'],
                'amount' => $this->lineReturnAmount($i),
            ]);
        }

        session()->flash('status', 'Return created.');

        return redirect()->route('crm.returns');
    }

    public function render()
    {
        return view('crm.returns.create')->layout('layouts.crm');
    }
}
