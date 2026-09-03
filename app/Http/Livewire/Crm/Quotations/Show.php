<?php

namespace App\Http\Livewire\Crm\Quotations;

use App\Models\Quotation;
use Livewire\Component;

class Show extends Component
{
    public Quotation $record;

    public function mount(Quotation $record): void
    {
        $this->record = $record->load(['items.product', 'items.vendor', 'customer', 'deal', 'staff']);
    }

    public function markSent(): void
    {
        $this->record->markSent();
        session()->flash('status', 'Quotation marked as sent.');
    }

    public function convert()
    {
        $invoice = $this->record->convertToInvoice(auth()->user());

        return redirect()->route('crm.invoices.show', $invoice);
    }

    public function render()
    {
        return view('crm.quotations.show')->layout('layouts.crm');
    }
}
