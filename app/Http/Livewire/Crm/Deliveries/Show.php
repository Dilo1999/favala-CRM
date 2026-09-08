<?php

namespace App\Http\Livewire\Crm\Deliveries;

use App\Http\Livewire\Concerns\EditsDeliveries;
use App\Models\Delivery;
use Livewire\Component;

class Show extends Component
{
    use EditsDeliveries;

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

    protected function afterDeliveryChange(): void
    {
        $this->record = $this->record->fresh(['items.product', 'customer', 'invoice']);
    }

    public function render()
    {
        return view('crm.deliveries.show')->layout('layouts.crm');
    }
}
