<?php

namespace App\Http\Livewire\Crm\Invoices;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\ReceiptVerificationService;
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

    /** Set when the uploaded receipt's printed reference doesn't match what was typed — null once there's nothing to warn about. */
    public ?string $referenceMismatchWarning = null;

    public function mount(Invoice $record): void
    {
        $this->record = $record->load(['items.product', 'customer', 'payments.receivedBy', 'quotation', 'deliveries', 'returns']);
    }

    public function openPaymentForm(): void
    {
        $this->paymentMethod = 'Cash';
        $this->paymentReference = null;
        $this->receiptFile = null;
        $this->referenceMismatchWarning = null;
        $this->showPaymentForm = true;
    }

    /** A mismatch warning refers to a specific reference/receipt pair — stale once either changes. */
    public function updatedPaymentReference(): void
    {
        $this->referenceMismatchWarning = null;
    }

    public function updatedReceiptFile(): void
    {
        $this->referenceMismatchWarning = null;
    }

    /**
     * What Receive Payment will actually charge. An invoice's own total
     * never changes once billed (see SalesReturn::applyRefundToInvoice()) —
     * a refund only ever affects what's been paid, via an offsetting
     * "Refund" payment, so balance_due already reflects the truth: it comes
     * back up to the full total on its own once a return is refunded, with
     * nothing special to account for here.
     */
    public function getPayableAmountProperty(): float
    {
        return round((float) $this->record->balance_due, 2);
    }

    protected function rules(): array
    {
        return [
            'paymentMethod' => 'required|in:'.implode(',', Payment::METHODS),
            // A non-Cash method (Bank Transfer, Cheque, Purchase Order) needs
            // something to reconcile it against — Cash doesn't. The receipt
            // is required alongside it since the reference is now verified
            // against what's actually printed on that file.
            'paymentReference' => $this->paymentMethod !== 'Cash' ? 'required|string|max:191' : 'nullable|string|max:191',
            'receiptFile' => $this->paymentMethod !== 'Cash' ? 'required|file|mimes:pdf,jpg,jpeg,png|max:5120' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function receivePayment(ReceiptVerificationService $verifier, bool $confirmMismatch = false): void
    {
        $this->validate();

        // Cross-check the reference number the user typed against what's
        // actually printed on the receipt they attached. Advisory, not a
        // hard gate: an inconclusive read (blurry photo, no reference
        // visible, API error) proceeds normally — only a confident mismatch
        // stops here, and only until the user explicitly confirms past it.
        if (! $confirmMismatch && $this->receiptFile && $this->paymentReference) {
            $result = $verifier->verify($this->receiptFile, $this->paymentReference);

            if ($result->isMismatch()) {
                $this->referenceMismatchWarning = "This receipt appears to show reference \"{$result->extractedReference}\", not \"{$this->paymentReference}\". Double-check before continuing.";

                return;
            }
        }

        $this->referenceMismatchWarning = null;

        $this->record->refresh();
        $amount = round((float) $this->record->balance_due, 2);

        if ($amount <= 0) {
            $this->showPaymentForm = false;
            session()->flash('status', 'Nothing was owed, so no payment was recorded.');

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
