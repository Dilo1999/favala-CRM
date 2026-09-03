<?php

namespace App\Http\Livewire\Crm\Invoices;

use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Component;

class Show extends Component
{
    public Invoice $record;

    public bool $showPaymentForm = false;

    public float $paymentAmount = 0;

    public string $paymentMethod = 'Cash';

    public ?string $paymentReference = null;

    public function mount(Invoice $record): void
    {
        $this->record = $record->load(['items.product', 'customer', 'payments.receivedBy', 'quotation', 'deliveries', 'returns']);
    }

    public function openPaymentForm(): void
    {
        $this->paymentAmount = (float) $this->record->balance_due;
        $this->paymentMethod = 'Cash';
        $this->paymentReference = null;
        $this->showPaymentForm = true;
    }

    protected function rules(): array
    {
        return [
            'paymentAmount' => 'required|numeric|min:0.01|max:'.max($this->record->balance_due, 0.01),
            'paymentMethod' => 'required|in:'.implode(',', Payment::METHODS),
            'paymentReference' => 'nullable|string|max:191',
        ];
    }

    public function receivePayment(): void
    {
        $this->validate();

        $this->record->receivePayment($this->paymentAmount, $this->paymentMethod, $this->paymentReference, auth()->user());
        $this->record->refresh();

        $this->showPaymentForm = false;
        session()->flash('status', 'Payment received.');
    }

    public function render()
    {
        return view('crm.invoices.show')->layout('layouts.crm');
    }
}
