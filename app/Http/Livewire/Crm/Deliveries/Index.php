<?php

namespace App\Http\Livewire\Crm\Deliveries;

use App\Http\Livewire\Concerns\EditsDeliveries;
use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Delivery;
use Livewire\Component;

class Index extends Component
{
    use EditsDeliveries, WithBasicTable;

    public function markComplete(int $id): void
    {
        Delivery::findOrFail($id)->markComplete();
        session()->flash('status', 'Delivery marked complete.');
    }

    public function delete(int $id): void
    {
        Delivery::findOrFail($id)->delete();
        session()->flash('status', 'Delivery deleted.');
    }

    public function render()
    {
        $deliveries = Delivery::with(['customer', 'invoice'])
            ->when($this->search, fn ($q) => $q->where('id', 'like', "%{$this->search}%")
                ->orWhere('location', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%")))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('crm.deliveries.index', ['deliveries' => $deliveries])->layout('layouts.crm');
    }
}
