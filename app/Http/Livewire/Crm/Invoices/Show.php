<?php

namespace App\Http\Livewire\Crm\Invoices;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalesReturn;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Invoice $record;

    public bool $showPaymentForm = false;

    public string $paymentMethod = 'Cash';

    public ?string $paymentReference = null;

    public $receiptFile = null;

    public function mount(Invoice $record): void
    {
        $this->record = $record->load(['items.product', 'customer', 'payments.receivedBy', 'quotation', 'deliveries', 'returns']);
    }

    public function openPaymentForm(): void
    {
        $this->paymentMethod = 'Cash';
        $this->paymentReference = null;
        $this->receiptFile = null;
        $this->showPaymentForm = true;
    }

    /** Returns on this invoice whose refund is still in effect — reversing these is what makes them payable again. */
    protected function refundedReturns()
    {
        return $this->record->returns()->whereNotNull('refund_applied_at')->get();
    }

    /**
     * What Receive Payment will actually charge: the current balance, plus
     * whatever's been refunded on this invoice — paying that again is how a
     * full or partial refund gets undone (the customer keeps the goods after
     * all), rather than a separate money-math path invented just for this.
     */
    public function getPayableAmountProperty(): float
    {
        return round((float) $this->record->balance_due + $this->refundedReturns()->sum('value'), 2);
    }

    protected function rules(): array
    {
        return [
            'paymentMethod' => 'required|in:'.implode(',', Payment::METHODS),
            // A non-Cash method (Bank Transfer, Cheque, Purchase Order) needs
            // something to reconcile it against — Cash doesn't.
            'paymentReference' => $this->paymentMethod !== 'Cash' ? 'required|string|max:191' : 'nullable|string|max:191',
            'receiptFile' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function receivePayment(): void
    {
        $this->validate();

        // Paying again after a refund undoes that return's effect first —
        // transitionTo() restores the invoice's subtotal/gst/grand_total and
        // removes the refund payment (SalesReturn::reverseRefundFromInvoice()),
        // and also flips the return's own status so it stops showing as
        // Refunded once its refund no longer holds.
        foreach ($this->refundedReturns() as $return) {
            $return->transitionTo(SalesReturn::STATUS_PROCESSED);
        }

        // Not user-editable — always the full balance due, re-read fresh here
        // rather than trusting a value set when the modal opened, in case
        // another payment (or refund reversal above) landed on this invoice
        // in the meantime.
        $amount = (float) $this->record->refresh()->balance_due;

        if ($amount <= 0) {
            $this->showPaymentForm = false;

            return;
        }

        $receiptPath = $this->receiptFile ? $this->receiptFile->store('payments/receipts', 'public') : null;

        $this->record->receivePayment($amount, $this->paymentMethod, $this->paymentReference, auth()->user(), $receiptPath);
        $this->record->refresh();

        $this->showPaymentForm = false;
        session()->flash('status', 'Payment received.');
    }

    public function render()
    {
        return view('crm.invoices.show')->layout('layouts.crm');
    }
}
