<?php

namespace App\Http\Livewire\Crm\Deals;

use App\Models\Deal;
use Livewire\Component;

class Show extends Component
{
    public Deal $record;

    public function mount(Deal $record): void
    {
        $this->record = $record->load(['customer', 'assignedStaff', 'createdBy', 'products.product', 'quotations']);
    }

    public function convertToQuotation()
    {
        return redirect()->route('crm.quotations.create', ['dealId' => $this->record->id]);
    }

    public function render()
    {
        return view('crm.deals.show')->layout('layouts.crm');
    }
}
