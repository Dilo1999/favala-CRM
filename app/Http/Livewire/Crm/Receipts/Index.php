<?php

namespace App\Http\Livewire\Crm\Receipts;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public function render()
    {
        $payments = Payment::with(['invoice.customer', 'receivedBy'])
            ->when($this->search, function ($q) {
                $paymentId = Payment::idFromFriendlyId($this->search);
                $invoiceId = Invoice::idFromFriendlyId($this->search);

                $q->where(function ($q) use ($paymentId, $invoiceId) {
                    $q->when($paymentId, fn ($q) => $q->orWhere('id', $paymentId))
                        ->orWhere('reference', 'like', "%{$this->search}%")
                        ->orWhereHas('invoice', function ($i) use ($invoiceId) {
                            $i->when($invoiceId, fn ($i) => $i->orWhere('id', $invoiceId))
                                ->orWhere('id', 'like', "%{$this->search}%")
                                ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%"));
                        });
                });
            })
            ->orderByDesc('date')
            ->paginate(20);

        return view('crm.receipts.index', ['payments' => $payments])->layout('layouts.crm');
    }
}
