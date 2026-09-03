<?php

namespace App\Http\Livewire\Crm\Queries;

use App\Models\SalesQuery;
use Livewire\Component;

class Detail extends Component
{
    public SalesQuery $record;

    public string $status;

    public bool $followUp;

    public function mount(SalesQuery $record): void
    {
        $this->record = $record->load(['customer', 'deal', 'quotation', 'assignedStaff']);
        $this->status = $record->status;
        $this->followUp = $record->follow_up;
    }

    public function save(): void
    {
        $this->record->update([
            'status' => $this->status,
            'follow_up' => $this->followUp,
        ]);

        session()->flash('status', 'Query updated.');
    }

    public function render()
    {
        return view('crm.queries.detail')->layout('layouts.crm');
    }
}
