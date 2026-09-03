<?php

namespace App\Http\Livewire\Crm\Returns;

use App\Models\Invoice;
use App\Models\SalesReturn;
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
            'lines.*.qty' => 'required|numeric|min:0',
        ];
    }

    public function getTotalValueProperty(): float
    {
        return collect($this->lines)->sum(fn ($l) => (float) $l['qty'] * (float) $l['rate']);
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

        foreach ($itemsToReturn as $line) {
            $return->items()->create([
                'product_id' => $line['product_id'],
                'qty' => $line['qty'],
                'amount' => $line['qty'] * $line['rate'],
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
