<?php

namespace App\Http\Livewire\Crm\Invoices;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public bool $showCreateModal = false;

    public array $form = [
        'customer_id' => null,
        'total_amount' => 0,
        'reference_number' => null,
        'staff_id' => null,
        'invoice_date' => '',
    ];

    public function openCreateModal(): void
    {
        $this->form = [
            'customer_id' => null,
            'total_amount' => 0,
            'reference_number' => null,
            'staff_id' => auth()->id(),
            'invoice_date' => now()->toDateString(),
        ];
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    protected function rules(): array
    {
        return [
            'form.customer_id' => 'required|exists:customers,id',
            'form.total_amount' => 'required|numeric|min:0.01',
            'form.reference_number' => 'nullable|string|max:255',
            'form.staff_id' => 'nullable|exists:users,id',
            'form.invoice_date' => 'required|date',
        ];
    }

    /**
     * Log an external/manual sale in one step: the user gives the final total
     * (inclusive of GST) and we back out the subtotal/GST split, with no
     * itemized product lines. Distinct from the itemized quotation -> invoice
     * conversion flow.
     */
    public function save()
    {
        $this->validate();

        $gstPercent = (float) config('crm.gst_percent');
        $total = (float) $this->form['total_amount'];
        $subtotal = round($total / (1 + $gstPercent / 100), 2);
        $gstAmount = round($total - $subtotal, 2);

        $invoice = Invoice::create([
            'customer_id' => $this->form['customer_id'],
            'staff_id' => $this->form['staff_id'],
            'reference_number' => $this->form['reference_number'],
            'invoice_date' => $this->form['invoice_date'],
            'subtotal' => $subtotal,
            'gst_percent' => $gstPercent,
            'gst_amount' => $gstAmount,
            'grand_total' => $total,
            'amount_paid' => 0,
            'balance_due' => $total,
            'payment_status' => 'pending',
        ]);

        session()->flash('status', 'Invoice created.');

        return redirect()->route('crm.invoices.show', $invoice);
    }

    public function delete(int $id): void
    {
        Invoice::findOrFail($id)->delete();
        session()->flash('status', 'Invoice deleted.');
    }

    public function render()
    {
        $invoices = Invoice::with(['customer', 'payments'])
            ->when($this->search, fn ($q) => $q->where('id', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%")))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('crm.invoices.index', [
            'invoices' => $invoices,
            'customers' => Customer::orderBy('company_name')->limit(300)->pluck('company_name', 'id'),
            'staff' => User::crmStaff()->orderBy('name')->pluck('name', 'id'),
        ])->layout('layouts.crm');
    }
}
