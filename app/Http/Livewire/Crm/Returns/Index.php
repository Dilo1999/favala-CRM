<?php

namespace App\Http\Livewire\Crm\Returns;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\SalesReturn;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    /** Pending/Processed only — reaching Refunded goes through approve() below. */
    public function updateStatus(int $id, string $status): void
    {
        abort_if($status === SalesReturn::STATUS_REFUNDED, 403);

        SalesReturn::findOrFail($id)->transitionTo($status);

        session()->flash('status', 'Return status updated.');
    }

    public function approve(int $id): void
    {
        abort_unless(auth()->user()->canApproveReturns(), 403);

        SalesReturn::findOrFail($id)->approve(auth()->user());

        session()->flash('status', 'Return approved and refunded — invoice balance/paid amount updated.');
    }

    public function reject(int $id): void
    {
        abort_unless(auth()->user()->canApproveReturns(), 403);

        SalesReturn::findOrFail($id)->reject(auth()->user());

        session()->flash('status', 'Return rejected.');
    }

    public function render()
    {
        $returns = SalesReturn::with(['customer', 'invoice'])
            ->when($this->search, fn ($q) => $q->where('id', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%"))
                ->orWhereHas('invoice', fn ($i) => $i->where('id', 'like', "%{$this->search}%")))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('crm.returns.index', ['returns' => $returns])->layout('layouts.crm');
    }
}
