<?php

namespace App\Http\Livewire\Crm\Deliveries;

use App\Models\Delivery;
use App\Models\Invoice;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public Invoice $invoice;

    public string $location = '';

    public string $contact_name = '';

    public string $contact_phone = '';

    public string $deadline_date;

    public ?string $deadline_time = null;

    public array $lines = [];

    public function mount(): void
    {
        $this->invoice = Invoice::with('items.product')->findOrFail(request()->query('invoiceId'));
        $this->contact_name = $this->invoice->bill_to_name ?? '';
        $this->contact_phone = $this->invoice->bill_to_phone ?? '';
        $this->location = $this->invoice->bill_to_address ?? '';
        $this->deadline_date = now()->toDateString();

        $deliveredByProduct = $this->invoice->deliveries()->with('items')->get()
            ->flatMap->items->groupBy('product_id')->map->sum('delivery_qty');

        $this->lines = $this->invoice->items->map(function ($item) use ($deliveredByProduct) {
            $delivered = $deliveredByProduct->get($item->product_id, 0);
            $balance = max($item->qty - $delivered, 0);

            return [
                'product_id' => $item->product_id,
                'label' => $item->product?->description,
                'balance_qty' => $balance,
                'delivery_qty' => $balance,
            ];
        })->all();
    }

    protected function rules(): array
    {
        return [
            'location' => 'required|string|max:191',
            'contact_name' => 'nullable|string|max:191',
            'contact_phone' => 'nullable|string|max:60',
            'deadline_date' => 'required|date',
            'deadline_time' => 'nullable',
            // Caps each line at what's actually still owed on the invoice — the
            // HTML max= on the input is client-side only.
            'lines.*.delivery_qty' => Rule::forEach(function ($value, $attribute) {
                preg_match('/^lines\.(\d+)\.delivery_qty$/', $attribute, $m);
                $max = (float) ($this->lines[(int) $m[1]]['balance_qty'] ?? 0);

                // Whole units only — products are counted, not measured.
                return ['required', 'integer', 'min:0', 'max:'.$max];
            }),
        ];
    }

    public function save()
    {
        $this->validate();

        $delivery = Delivery::create([
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->invoice->customer_id,
            'created_by' => auth()->id(),
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'location' => $this->location,
            'deadline_date' => $this->deadline_date,
            'deadline_time' => $this->deadline_time ?: null,
            'status' => Delivery::STATUS_PENDING,
        ]);

        foreach ($this->lines as $line) {
            if ((float) $line['delivery_qty'] <= 0) {
                continue;
            }
            $delivery->items()->create([
                'product_id' => $line['product_id'],
                'balance_qty' => $line['balance_qty'],
                'delivery_qty' => $line['delivery_qty'],
            ]);
        }

        session()->flash('status', 'Delivery note created.');

        return redirect()->route('crm.deliveries.show', $delivery);
    }

    public function render()
    {
        return view('crm.deliveries.create')->layout('layouts.crm');
    }
}
