<?php

namespace App\Http\Livewire\Crm\Quotations;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Quotation;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public string $statusFilter = '';

    public function markSent(int $id): void
    {
        Quotation::findOrFail($id)->markSent();
        session()->flash('status', 'Quotation marked as sent.');
    }

    public function convert(int $id)
    {
        $quotation = Quotation::findOrFail($id);
        $invoice = $quotation->convertToInvoice(auth()->user());
        session()->flash('status', 'Quotation converted to invoice.');

        return redirect()->route('crm.invoices.show', $invoice);
    }

    public function delete(int $id): void
    {
        Quotation::findOrFail($id)->delete();
        session()->flash('status', 'Quotation deleted.');
    }

    public function render()
    {
        $quotations = Quotation::with(['deal', 'customer', 'staff'])
            ->when($this->search, fn ($q) => $q->where('id', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%"))
                ->orWhereHas('staff', fn ($s) => $s->where('name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('crm.quotations.index', ['quotations' => $quotations])->layout('layouts.crm');
    }
}
