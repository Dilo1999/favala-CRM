<?php

namespace App\Http\Livewire\Crm\Deals;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Deal;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public string $tab = 'all';

    public string $viewMode = 'list';

    protected function baseQuery()
    {
        return Deal::with(['customer', 'assignedStaff'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->whereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%"))
                    ->orWhereHas('assignedStaff', fn ($s) => $s->where('name', 'like', "%{$this->search}%"))
                    ->orWhere('id', 'like', "%{$this->search}%");
            }));
    }

    public function getKpisProperty(): array
    {
        return [
            'in_progress' => Deal::whereIn('stage', ['potential', 'hot'])->whereNull('converted_at')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))->count(),
            'potential' => Deal::where('stage', 'potential')->count(),
            'hot' => Deal::where('stage', 'hot')->count(),
            'mine' => Deal::where('assigned_staff_id', auth()->id())->count(),
            'converted' => Deal::where('stage', 'won')->count(),
        ];
    }

    public function render()
    {
        $query = $this->baseQuery();

        match ($this->tab) {
            'in_progress' => $query->whereIn('stage', ['potential', 'hot'])->whereNull('converted_at'),
            'converted' => $query->where('stage', 'won'),
            'expired' => $query->whereIn('stage', ['potential', 'hot'])->whereNull('converted_at')->where('expires_at', '<', now()),
            default => null,
        };

        $deals = $query->orderByDesc('created_at')->paginate(15);

        return view('crm.deals.index', ['deals' => $deals])->layout('layouts.crm');
    }
}
