<?php

namespace App\Http\Livewire\Crm\Receipts;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Payment;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public function render()
    {
        $payments = Payment::with(['invoice.customer', 'receivedBy'])
            ->when($this->search, fn ($q) => $q->where('reference', 'like', "%{$this->search}%")
                ->orWhereHas('invoice', fn ($i) => $i->where('id', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%"))))
            ->orderByDesc('date')
            ->paginate(20);

        return view('crm.receipts.index', ['payments' => $payments])->layout('layouts.crm');
    }
}
