<?php

namespace App\Http\Livewire\Crm\Returns;

use App\Models\SalesReturn;
use Livewire\Component;

class Show extends Component
{
    public SalesReturn $record;

    public function mount(SalesReturn $record): void
    {
        $this->record = $record->load(['items.product', 'customer', 'invoice', 'createdBy']);
    }

    public function updateStatus(string $status): void
    {
        $this->record->transitionTo($status);
        $this->record->refresh();

        session()->flash(
            'status',
            $status === SalesReturn::STATUS_REFUNDED
                ? 'Return marked Refunded — invoice balance/paid amount updated.'
                : 'Return status updated.'
        );
    }

    public function render()
    {
        return view('crm.returns.show')->layout('layouts.crm');
    }
}
