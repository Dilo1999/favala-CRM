<?php

namespace App\Http\Livewire\Crm\Invoices;

use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Component;

class Show extends Component
{
    public Invoice $record;

    public bool $showPaymentForm = false;

    public string $paymentMethod = 'Cash';

    public ?string $paymentReference = null;

    public function mount(Invoice $record): void
    {
        $this->record = $record->load(['items.product', 'customer', 'payments.receivedBy', 'quotation', 'deliveries', 'returns']);
    }

    public function openPaymentForm(): void
    {
        $this->paymentMethod = 'Cash';
        $this->paymentReference = null;
        $this->showPaymentForm = true;
    }

    protected function rules(): array
    {
        return [
            'paymentMethod' => 'required|in:'.implode(',', Payment::METHODS),
            'paymentReference' => 'nullable|string|max:191',
        ];
    }

    public function receivePayment(): void
    {
        $this->validate();

        // Not user-editable — always the full balance due, re-read fresh here
        // rather than trusting a value set when the modal opened, in case
        // another payment landed on this invoice in the meantime.
        $amount = (float) $this->record->refresh()->balance_due;

        if ($amount <= 0) {
            $this->showPaymentForm = false;

            return;
        }

        $this->record->receivePayment($amount, $this->paymentMethod, $this->paymentReference, auth()->user());
        $this->record->refresh();

        $this->showPaymentForm = false;
        session()->flash('status', 'Payment received.');
    }

    public function render()
    {
        return view('crm.invoices.show')->layout('layouts.crm');
    }
}
