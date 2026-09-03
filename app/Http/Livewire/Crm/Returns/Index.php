<?php

namespace App\Http\Livewire\Crm\Returns;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\SalesReturn;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public function updateStatus(int $id, string $status): void
    {
        SalesReturn::whereKey($id)->update(['status' => $status]);
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
