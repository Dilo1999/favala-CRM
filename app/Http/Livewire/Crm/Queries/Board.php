<?php

namespace App\Http\Livewire\Crm\Queries;

use App\Models\Customer;
use App\Models\SalesQuery;
use Livewire\Component;

class Board extends Component
{
    public string $search = '';

    public string $timeFilter = 'all';

    public bool $showNewQueryForm = false;

    public array $newQuery = ['customer_id' => null, 'description' => '', 'query_source' => null, 'query_type' => null];

    protected function baseQuery()
    {
        $query = SalesQuery::query()->with(['customer', 'assignedStaff']);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->whereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%"))
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        match ($this->timeFilter) {
            'today' => $query->whereDate('created_at', now()->toDateString()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            default => null,
        };

        return $query;
    }

    public function getColumnsProperty(): array
    {
        $all = $this->baseQuery()->latest()->get();

        return [
            SalesQuery::STATUS_NEW => $all->where('status', SalesQuery::STATUS_NEW)->values(),
            SalesQuery::STATUS_NEGOTIATING => $all->where('status', SalesQuery::STATUS_NEGOTIATING)->values(),
            SalesQuery::STATUS_COMPLETED => $all->where('status', SalesQuery::STATUS_COMPLETED)->values(),
            SalesQuery::STATUS_DEAD => $all->where('status', SalesQuery::STATUS_DEAD)->values(),
        ];
    }

    public function getKpisProperty(): array
    {
        $counts = $this->baseQuery()->get()->countBy('status');

        return [
            'total' => $counts->sum(),
            'new' => $counts->get(SalesQuery::STATUS_NEW, 0),
            'negotiating' => $counts->get(SalesQuery::STATUS_NEGOTIATING, 0),
            'completed' => $counts->get(SalesQuery::STATUS_COMPLETED, 0),
        ];
    }

    public function getCustomersProperty()
    {
        return Customer::orderBy('company_name')->limit(200)->pluck('company_name', 'id');
    }

    public function moveTo(int $queryId, string $status): void
    {
        SalesQuery::whereKey($queryId)->update(['status' => $status]);
    }

    public function createQuery(): void
    {
        $this->validate([
            'newQuery.customer_id' => 'required|exists:customers,id',
            'newQuery.description' => 'nullable|string',
        ]);

        $customer = Customer::find($this->newQuery['customer_id']);

        SalesQuery::create([
            'customer_id' => $customer->id,
            'phone' => $customer->phone,
            'description' => $this->newQuery['description'],
            'tags' => [],
            'source' => 'manual',
            'status' => SalesQuery::STATUS_NEW,
            'query_source' => $this->newQuery['query_source'],
            'query_type' => $this->newQuery['query_type'],
            'assigned_staff_id' => auth()->id(),
        ]);

        $this->newQuery = ['customer_id' => null, 'description' => '', 'query_source' => null, 'query_type' => null];
        $this->showNewQueryForm = false;
    }

    public function render()
    {
        return view('crm.queries.board')->layout('layouts.crm');
    }
}
