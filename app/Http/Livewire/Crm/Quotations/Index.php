<?php

namespace App\Http\Livewire\Crm\Quotations;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Services\ProductStockService;
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

    /**
     * A quotation's invoice(s) with any payment activity (partial/paid/refunded)
     * block the delete outright — cascading past them would destroy real
     * Payment records (payments.invoice_id is cascadeOnDelete). A still-pending
     * invoice (no payments at all) is safe to remove along with the quotation,
     * and since it represents stock that was decremented at invoice creation
     * but never actually sold, that stock is restored.
     */
    public function delete(int $id): void
    {
        abort_unless(auth()->user()->canManageAllRecords(), 403);

        $quotation = Quotation::with('invoices.items')->findOrFail($id);

        $blocking = $quotation->invoices->firstWhere('payment_status', '!=', Invoice::STATUS_PENDING);

        if ($blocking) {
            session()->flash('error', "Can't delete this quotation — its invoice {$blocking->friendly_id} has payment activity ({$blocking->payment_status}). Only quotations with no paid invoice can be deleted.");

            return;
        }

        foreach ($quotation->invoices as $invoice) {
            app(ProductStockService::class)->restore($invoice->items);
            $invoice->delete();
        }

        $quotation->delete();
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
