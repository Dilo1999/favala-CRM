<?php

namespace App\Http\Livewire\Crm\Deliveries;

use App\Models\Delivery;
use Livewire\Component;

class Show extends Component
{
    public Delivery $record;

    public function mount(Delivery $record): void
    {
        $this->record = $record->load(['items.product', 'customer', 'invoice']);
    }

    public function markComplete(): void
    {
        $this->record->markComplete();
        session()->flash('status', 'Delivery marked complete.');
    }

    public function render()
    {
        return view('crm.deliveries.show')->layout('layouts.crm');
    }
}
