<?php

namespace App\Http\Livewire\Crm\Returns;

use App\Models\SalesReturn;
use Livewire\Component;

class Show extends Component
{
    public SalesReturn $record;

    public function mount(SalesReturn $record): void
    {
        $this->record = $record->load(['items.product', 'customer', 'invoice', 'createdBy', 'approvedBy']);
    }

    /**
     * Pending/Processed only — reaching Refunded goes through approve() below.
     * Leaving Refunded needs the same management gate as reaching it: it
     * un-does approve()'s refund (invoice credit, payment, restored stock),
     * so it can't be left open to anyone who can merely view the return.
     */
    public function updateStatus(string $status): void
    {
        abort_if($status === SalesReturn::STATUS_REFUNDED, 403);
        abort_if($this->record->status === SalesReturn::STATUS_REFUNDED && ! auth()->user()->canApproveReturns(), 403);

        $this->record->transitionTo($status);
        $this->record->refresh();

        session()->flash('status', 'Return status updated.');
    }

    public function approve(): void
    {
        abort_unless(auth()->user()->canApproveReturns(), 403);

        $this->record->approve(auth()->user());
        $this->record->refresh();

        session()->flash('status', 'Return approved and refunded — invoice balance/paid amount updated.');
    }

    public function reject(): void
    {
        abort_unless(auth()->user()->canApproveReturns(), 403);

        $this->record->reject(auth()->user());
        $this->record->refresh();

        session()->flash('status', 'Return rejected.');
    }

    public function render()
    {
        return view('crm.returns.show')->layout('layouts.crm');
    }
}
