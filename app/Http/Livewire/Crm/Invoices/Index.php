<?php

namespace App\Http\Livewire\Crm\Invoices;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Invoice;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

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

        return view('crm.invoices.index', ['invoices' => $invoices])->layout('layouts.crm');
    }
}
